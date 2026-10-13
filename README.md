# SecureShare — Secure Document Sharing Platform

SecureShare is a full-stack document sharing platform built with Laravel and React. It enables authenticated users to upload and manage private documents, share them with accepted friends using read or write permissions, and control access through expiration and revocation.

The platform also includes friendship management, in-app and push notifications, administrative user management, and audit logging to support secure document collaboration.

## Features

### Document Management
- Upload and manage private documents.
- Browse personal documents and documents shared by other users.
- Download documents through authenticated API endpoints.
- Replace existing documents and update document information.
- Soft-delete documents while preserving database records.
- Store uploaded files on private storage rather than exposing them through public URLs.

### Secure Document Sharing
- Share documents with accepted friends.
- Assign read or write permissions to individual users.
- Set optional expiration dates for shared access.
- Revoke access to previously shared documents.
- View documents shared with the current user.
- Prevent duplicate active shares for the same document and recipient.
- Enforce access permissions through Laravel policies and server-side validation.

### Authentication and Authorization
- User registration and login.
- Session-based authentication using Laravel Sanctum.
- CSRF protection for stateful requests.
- Authenticated logout and current-user retrieval.
- Role-based access control for administrative operations.
- Protection against unauthorized document access.

### Friendship Management
- Send friend requests to other users.
- Accept or reject incoming requests.
- View friends and pending requests.
- Remove existing friendships.
- Validate recipients before allowing document sharing.

### Notifications
- Database-backed in-app notifications.
- Mark individual notifications as read or mark all as read.
- Firebase Cloud Messaging (FCM) push notification integration.
- Friend-request notifications.
- A notification testing endpoint.

### Administration and Audit Logging
- Administrative dashboard and user management.
- Search, filter, and paginate user records.
- Edit user information and roles.
- Activate or deactivate user accounts.
- Soft-delete and restore users.
- View filtered and paginated audit logs.
- Record important actions, including document uploads, downloads, sharing, revocation, updates, and deletion.

## Technology Stack

### Backend
- **PHP 8.3+** — Server-side programming.
- **Laravel 13** — Backend framework and REST API.
- **Laravel Sanctum** — Session-based authentication and API token support.
- **PostgreSQL** — Recommended database configuration.
- **L5-Swagger** — OpenAPI documentation generation.
- **Pest** — Backend automated testing.
- **Firebase Admin SDK** — Firebase Cloud Messaging integration.

### Frontend
- **React 19** — Component-based user interface.
- **JavaScript and JSX** — Frontend application development.
- **Axios** — HTTP requests to the backend API.
- **Tailwind CSS 4** — Responsive UI styling.
- **Vite** — Frontend development and asset bundling.
- **Firebase JavaScript SDK** — Client-side push notification integration.

## Architecture

SecureShare uses a single Laravel application to serve both the backend API and the React single-page application (SPA).

The frontend and backend are organized into separate logical layers within the same repository rather than being maintained as independent applications.

- **Laravel API:** Handles authentication, validation, business logic, document access, friendships, notifications, administration, and audit logging.
- **React SPA:** Provides the user dashboard, document management interface, sharing workflows, friendship features, and administrative interface.
- **Database:** Stores users, documents, sharing permissions, friendships, notifications, and audit records.
- **Private File Storage:** Stores uploaded documents outside the public web directory.
- **External Services:** OpenAPI tooling and Firebase Cloud Messaging support API documentation and push notifications.

## Project Structure

```text
SecureShare/
├── app/
│   ├── Actions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   ├── Notifications/
│   ├── OpenApi/
│   ├── Policies/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── api/
│   │   └── main.jsx
│   └── views/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── storage/
├── tests/
│   ├── Feature/
│   └── Unit/
├── composer.json
├── package.json
└── vite.config.js
```

## Security and Access Control

Security is a core part of SecureShare's design.

- **Private file storage:** Uploaded documents are not intended to be publicly accessible.
- **Policy-based authorization:** Document access is restricted to owners and users with valid sharing permissions.
- **Permission enforcement:** Read and write access is checked on the server.
- **Expiring shares:** Shared access can expire automatically.
- **Revocable access:** Owners can revoke active shares.
- **Database integrity:** Transactions, row locking, and a partial unique index help prevent duplicate active shares under concurrent requests.
- **Rate limiting:** Sensitive endpoints use request throttling to reduce abuse.
- **Administrative protection:** Admin-only middleware restricts privileged operations.
- **Audit logging:** Important document and administrative actions are recorded for accountability.
- **Soft deletion:** Selected records can be deleted without immediately removing their database history.

Authorization is enforced by the backend rather than relying on frontend visibility or client-side checks.

## API Overview

The REST API uses the `/api` prefix and returns JSON responses.

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/api/register` | Register a user |
| `POST` | `/api/login` | Authenticate a user |
| `GET` | `/api/user` | Retrieve the authenticated user |
| `POST` | `/api/logout` | Log out |
| `GET` | `/api/documents` | List personal documents |
| `POST` | `/api/documents` | Upload a document |
| `GET` | `/api/documents/{document}` | Retrieve document information |
| `PUT` / `POST` | `/api/documents/{document}` | Update or replace a document |
| `DELETE` | `/api/documents/{document}` | Delete a document |
| `GET` | `/api/documents/{document}/download` | Download a document |
| `GET` / `POST` | `/api/documents/{document}/shares` | List or create document shares |
| `DELETE` | `/api/documents/{document}/shares/{share}` | Revoke a share |
| `GET` | `/api/documents/shared-with-me` | List documents shared with the user |
| `POST` / `GET` | `/api/friend-requests` | Send or list friend requests |
| `GET` | `/api/friends` | List friends |
| `GET` | `/api/notifications` | Retrieve notifications |
| `GET` | `/api/admin/audit-logs` | Retrieve audit logs (admin only) |
| `GET` | `/api/admin/users` | List users (admin only) |

Additional endpoints support accepting and rejecting friend requests, managing notification read status, device tokens, and administrative user operations.

Most protected endpoints require authentication. Administrative endpoints additionally require administrator privileges.

### API Response Format

API responses generally follow a consistent JSON envelope:

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {}
}
```

Paginated responses may also include metadata such as the current page, last page, page size, and total number of records.

The example above illustrates the response structure; actual messages and data depend on the endpoint.

## Getting Started

### Prerequisites

Ensure the following tools are installed:

- PHP 8.3 or later.
- Composer.
- Node.js and npm.
- PostgreSQL or another database configuration verified to support the project's migrations.
- A compatible web browser.

Firebase credentials are required only when configuring the push notification integration.

### 1. Clone the Repository

```bash
git clone YOUR_REPOSITORY_URL
cd YOUR_PROJECT_DIRECTORY
```

Replace the placeholders with the actual repository URL and directory name.

### 2. Install Dependencies

Install the PHP dependencies:

```bash
composer install
```

Install the frontend dependencies:

```bash
npm install
```

### 3. Configure the Environment

Create your local environment file:

```bash
cp .env.example .env
```

On Windows Command Prompt, you can use:

```cmd
copy .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Configure the required environment variables in `.env`, including:

- Application settings.
- Database connection details.
- Session and Sanctum stateful domain settings.
- Cache, queue, and filesystem configuration.
- Firebase credentials, if push notifications are enabled.

Use local development credentials and never commit secrets to version control.

### 4. Configure the Database

Create the database and configure the database connection in `.env`.

The supplied example configuration uses PostgreSQL. Review the project's migrations before selecting a different database engine, particularly because document sharing relies on a PostgreSQL-oriented partial unique index.

Run the migrations and seed the development administrator account:

```bash
php artisan migrate --seed
```

Review `database/seeders/AdminUserSeeder.php` for the development administrator setup. Change any default development credentials before using the application outside a local environment.

### 5. Start the Application

Start the Laravel development server:

```bash
php artisan serve
```

In a separate terminal, start the Vite development server:

```bash
npm run dev
```

Open the application at:

[http://127.0.0.1:8000/app](http://127.0.0.1:8000/app)

The Vite configuration proxies API and Sanctum requests to the local Laravel server. Ensure the configured origins, stateful domains, and frontend API URL match your local setup.

### 6. API Documentation

SecureShare uses L5-Swagger and OpenAPI annotations.

Generate the API documentation:

```bash
php artisan l5-swagger:generate
```

When documentation generation is configured successfully, the Swagger UI is available at:

[http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation)

The OpenAPI annotations are maintained under `app/OpenApi/`.

## Testing

The backend uses Pest for automated tests.

Run the test suite:

```bash
php artisan test
```

Or use the Composer test script:

```bash
composer test
```

The existing feature tests cover important areas such as authentication, document sharing, access control, friendship workflows, notifications, administrative user management, and audit logs.

The test configuration uses an in-memory SQLite database. Passing these tests does not replace testing the application against its intended PostgreSQL configuration.

Automated frontend tests are not currently included.

## Current Limitations

SecureShare is an actively developed project. The following limitations should be considered:

- Password reset and email verification endpoints are not implemented.
- Document deletion is available through the API but is not exposed in the current frontend interface.
- Dashboard notifications are periodically polled; real-time broadcasting is not implemented.
- Firebase device tokens are stored in the application cache, so their persistence depends on the configured cache backend.
- The document API resource includes a `name` field that does not correspond to a column in the current documents table; the actual filename is stored as `original_name`.
- The generated Swagger output and production deployment configuration have not been independently verified.
- Frontend automated tests are not currently available.

These limitations distinguish implemented functionality from areas that may require further development.

## Future Improvements

Potential improvements include:

- Implement password reset and email verification.
- Add automated frontend tests.
- Expose document deletion in the user interface.
- Persist Firebase device tokens in a dedicated database table.
- Introduce real-time notifications using broadcasting.
- Improve API documentation consistency with the SPA's session-based authentication flow.
- Add production deployment automation and continuous integration.
- Expand monitoring, logging, and security testing.

## License

No license has been specified in the project information provided. Unless a license is added to the repository, the project should be treated as having all rights reserved by its owner.