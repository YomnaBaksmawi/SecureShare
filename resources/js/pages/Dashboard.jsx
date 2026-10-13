
import { useEffect, useRef, useState } from 'react';
import api from '../api/client';

import Sidebar from '../components/Sidebar';
import Header from '../components/Header';
import Stats from '../components/Stats';
import DocumentList from '../components/DocumentList';
import SharedDocuments from '../components/SharedDocuments';
import FriendRequests from '../components/FriendRequests';
import Friends from '../components/Friends';
import DocumentSharing from './DocumentSharing';

import { requestNotificationPermission } from '../notifications';

function Dashboard({ user, onLogout }) {
    console.log('DASHBOARD LOADED');

    const [documents, setDocuments] = useState([]);
    const [users, setUsers] = useState([]);
    const [sharedDocuments, setSharedDocuments] = useState([]);
    const [loading, setLoading] = useState(true);

    const [uploading, setUploading] = useState(false);
    const [uploadError, setUploadError] = useState('');

    const [replacing, setReplacing] = useState(null);
    const [replaceError, setReplaceError] = useState('');

    const [sharingDocument, setSharingDocument] = useState(null);

    const [notifications, setNotifications] = useState([]);
    const [notificationsLoading, setNotificationsLoading] = useState(true);
    const [actingRequestId, setActingRequestId] = useState(null);
    const [requestRefreshKey, setRequestRefreshKey] = useState(0);

    const [activeSection, setActiveSection] = useState('dashboard');

    const fileInputRef = useRef(null);
    const replaceFileInputRef = useRef(null);
    const replaceDocumentRef = useRef(null);

    useEffect(() => {
        console.log('NOTIFICATION TEST STARTED');

        requestNotificationPermission()
            .then((token) => {
                console.log(
                    'PERMISSION RESULT:',
                    Notification.permission
                );
                console.log('FCM TOKEN:', token);
            })
            .catch((error) => {
                console.error('FCM ERROR:', error);
            });
    }, []);

    useEffect(() => {
        const loadDashboardData = async () => {
            try {
                const [
                    documentsResponse,
                    usersResponse,
                    sharedDocumentsResponse,
                ] = await Promise.all([
                    api.get('/documents'),
                    api.get('/users/shareable'),
                    api.get('/documents/shared-with-me'),
                ]);

                setDocuments(documentsResponse.data.data);
                setUsers(usersResponse.data.data);
                setSharedDocuments(sharedDocumentsResponse.data.data);
            } catch (error) {
                console.error(error);
            } finally {
                setLoading(false);
            }
        };

        loadDashboardData();
    }, []);

    const loadNotifications = async () => {
        try {
            const response = await api.get('/notifications');

            setNotifications(response.data.data || []);
        } catch (error) {
            console.error(error);
        } finally {
            setNotificationsLoading(false);
        }
    };

    useEffect(() => {
        loadNotifications();

        const interval = setInterval(loadNotifications, 15000);

        return () => clearInterval(interval);
    }, []);

    const markAsRead = async (notification) => {
        if (notification.read_at) {
            return;
        }

        try {
            await api.patch(`/notifications/${notification.id}/read`);

            setNotifications((currentNotifications) =>
                currentNotifications.map((currentNotification) =>
                    currentNotification.id === notification.id
                        ? {
                              ...currentNotification,
                              read_at: new Date().toISOString(),
                          }
                        : currentNotification
                )
            );
        } catch (error) {
            console.error(error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await api.patch('/notifications/read-all');

            setNotifications((currentNotifications) =>
                currentNotifications.map((currentNotification) =>
                    currentNotification.read_at
                        ? currentNotification
                        : {
                              ...currentNotification,
                              read_at: new Date().toISOString(),
                          }
                )
            );
        } catch (error) {
            console.error(error);
        }
    };

    const handleRequestAction = async (friendshipId, action) => {
        setActingRequestId(friendshipId);

        try {
            await api.post(
                `/friend-requests/${friendshipId}/${action}`
            );

            setRequestRefreshKey((currentKey) => currentKey + 1);

            await loadNotifications();
        } catch (error) {
            console.error(error);
        } finally {
            setActingRequestId(null);
        }
    };

    const handleUploadClick = () => {
        fileInputRef.current?.click();
    };

    const handleUpload = async (event) => {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        setUploading(true);
        setUploadError('');

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await api.post('/documents', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            setDocuments((currentDocuments) => [
                response.data.data,
                ...currentDocuments,
            ]);
        } catch (error) {
            console.error(error);

            setUploadError(
                error.response?.data?.message ||
                    'Unable to upload the document.'
            );
        } finally {
            setUploading(false);
            event.target.value = '';
        }
    };

    const handleReplace = (document) => {
        setReplaceError('');
        replaceDocumentRef.current = document;

        replaceFileInputRef.current?.click();
    };

    const handleReplaceFile = async (event) => {
        const file = event.target.files?.[0];
        const document = replaceDocumentRef.current;

        if (!file || !document) {
            return;
        }

        setReplacing(document.id);
        setReplaceError('');

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await api.post(
                `/documents/${document.id}`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );

            const updatedDocument = response.data.data;

            setDocuments((currentDocuments) =>
                currentDocuments.map((currentDocument) =>
                    currentDocument.id === updatedDocument.id
                        ? updatedDocument
                        : currentDocument
                )
            );

            setSharedDocuments((currentDocuments) =>
                currentDocuments.map((currentDocument) =>
                    currentDocument.id === updatedDocument.id
                        ? {
                              ...currentDocument,
                              ...updatedDocument,
                          }
                        : currentDocument
                )
            );

            setSharingDocument((currentDocument) =>
                currentDocument?.id === updatedDocument.id
                    ? {
                          ...currentDocument,
                          ...updatedDocument,
                      }
                    : currentDocument
            );
        } catch (error) {
            console.error(error);

            setReplaceError(
                error.response?.data?.message ||
                    'Unable to replace the document.'
            );
        } finally {
            setReplacing(null);
            replaceDocumentRef.current = null;
            event.target.value = '';
        }
    };

    const handleDownload = async (document) => {
        try {
            const response = await api.get(
                `/documents/${document.id}/download`,
                {
                    responseType: 'blob',
                }
            );

            const url = window.URL.createObjectURL(
                new Blob([response.data])
            );

            const link = window.document.createElement('a');

            link.href = url;
            link.setAttribute(
                'download',
                document.original_name
            );

            window.document.body.appendChild(link);
            link.click();
            link.remove();

            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error(error);
        }
    };

    const openSharePage = (document) => {
        setSharingDocument(document);
    };

    const closeSharePage = () => {
        setSharingDocument(null);
    };

    const handleLogout = () => {
        onLogout?.();
    };

    const handleSectionChange = (section) => {
        setSharingDocument(null);
        setActiveSection(section);
    };

    if (sharingDocument) {
        return (
            <DocumentSharing
                document={sharingDocument}
                users={users}
                onBack={closeSharePage}
                onLogout={handleLogout}
                onSectionChange={handleSectionChange}
                activeSection={activeSection}
            />
        );
    }

    return (
        <div className="min-h-screen bg-slate-50">
            <Sidebar
                activeSection={activeSection}
                onSectionChange={handleSectionChange}
                onLogout={handleLogout}
            />

            <main className="lg:ml-64">
                <Header
                    user={user}
                    notifications={notifications}
                    notificationsLoading={notificationsLoading}
                    onMarkAsRead={markAsRead}
                    onMarkAllAsRead={markAllAsRead}
                    onAcceptRequest={(friendshipId) =>
                        handleRequestAction(friendshipId, 'accept')
                    }
                    onRejectRequest={(friendshipId) =>
                        handleRequestAction(friendshipId, 'reject')
                    }
                    actingRequestId={actingRequestId}
                />

                <div className="p-6 lg:p-10">
                    {activeSection === 'dashboard' && (
                        <>
                            <Stats
                                documentsCount={documents.length}
                                usersCount={users.length}
                            />

                            <div className="mt-8">
                                <DocumentList
                                    documents={documents}
                                    loading={loading}
                                    uploadError={uploadError}
                                    uploading={uploading}
                                    fileInputRef={fileInputRef}
                                    replaceFileInputRef={
                                        replaceFileInputRef
                                    }
                                    onUpload={handleUpload}
                                    onUploadClick={handleUploadClick}
                                    onDownload={handleDownload}
                                    onShare={openSharePage}
                                    onReplace={handleReplace}
                                    onReplaceFile={handleReplaceFile}
                                    replacing={replacing}
                                />
                            </div>

                            <div className="mt-8">
                                <SharedDocuments
                                    documents={sharedDocuments}
                                    onDownload={handleDownload}
                                    onReplace={handleReplace}
                                    replacing={replacing}
                                    replaceFileInputRef={
                                        replaceFileInputRef
                                    }
                                    onReplaceFile={handleReplaceFile}
                                />
                            </div>
                        </>
                    )}

                    {activeSection === 'documents' && (
                        <>
                            <div className="mb-8">
                                <p className="text-sm font-medium text-slate-500">
                                    Documents
                                </p>

                                <h1 className="mt-2 text-2xl font-bold text-slate-900">
                                    My Documents
                                </h1>

                                <p className="mt-2 text-sm text-slate-500">
                                    Manage the documents you have uploaded.
                                </p>
                            </div>

                            <DocumentList
                                documents={documents}
                                loading={loading}
                                uploadError={uploadError}
                                uploading={uploading}
                                fileInputRef={fileInputRef}
                                replaceFileInputRef={
                                    replaceFileInputRef
                                }
                                onUpload={handleUpload}
                                onUploadClick={handleUploadClick}
                                onDownload={handleDownload}
                                onShare={openSharePage}
                                onReplace={handleReplace}
                                onReplaceFile={handleReplaceFile}
                                replacing={replacing}
                            />
                        </>
                    )}

                    {activeSection === 'shared' && (
                        <>
                            <div className="mb-8">
                                <p className="text-sm font-medium text-slate-500">
                                    Documents
                                </p>

                                <h1 className="mt-2 text-2xl font-bold text-slate-900">
                                    Shared with me
                                </h1>

                                <p className="mt-2 text-sm text-slate-500">
                                    Documents that other users have shared
                                    with you.
                                </p>
                            </div>

                            <SharedDocuments
                                documents={sharedDocuments}
                                onDownload={handleDownload}
                                onReplace={handleReplace}
                                replacing={replacing}
                                replaceFileInputRef={
                                    replaceFileInputRef
                                }
                                onReplaceFile={handleReplaceFile}
                            />
                        </>
                    )}

                    {activeSection === 'friends' && (
                        <Friends
                            onShowRequests={() =>
                                handleSectionChange('friend-requests')
                            }
                        />
                    )}

                    {activeSection === 'friend-requests' && (
                        <FriendRequests
                            refreshKey={requestRefreshKey}
                            actingId={actingRequestId}
                            onAccept={(friendshipId) =>
                                handleRequestAction(friendshipId, 'accept')
                            }
                            onReject={(friendshipId) =>
                                handleRequestAction(friendshipId, 'reject')
                            }
                            users={users}
                            currentUserId={user?.id}
                        />
                    )}

                    {replaceError && (
                        <div className="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {replaceError}
                        </div>
                    )}
                </div>
            </main>
        </div>
    );
}

export default Dashboard;

