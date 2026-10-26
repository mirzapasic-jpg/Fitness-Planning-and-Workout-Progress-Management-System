# Fitness Planning and Workout Progress Management System

## Project Description
A web application where users can create workout plans,
manage exercises, log completed workouts, and track progress.

## Technology Stack
Frontend: HTML, CSS, JavaScript, Bootstrap (optional)
Backend: PHP with FlightPHP
Database: MySQL using PDO

## Architecture
- Keep frontend and backend separate.
- Frontend uses HTML, CSS and JavaScript.
- Backend uses FlightPHP REST API routes.
- Services contain business logic.
- DAO classes handle database operations.
- All frontend/backend communication uses AJAX.

## Folder Structure
- frontend/ - User interface
- backend/routes/ - API endpoints
- backend/services/ - Business logic
- backend/dao/ - Database operations
- backend/middleware/ - Middleware
- backend/config/ - Configuration
- backend/logs/ - Logs
- docs/ - Project documentation

## Coding Rules
- Use descriptive file and function names.
- Keep code modular and readable.
- Never hardcode passwords or secret keys.
- Use prepared SQL statements.
- Do not mix frontend HTML with backend PHP.
- Implement the frontend as a single-page application.
## Milestone 1 implementation conventions
- Current frontend uses HTML templates fetched by a hash router in frontend/js/app.js. Shared mock data and statistics live in frontend/js/data.js.
- Templates contain structure; application events/rendering are centralized. Do not duplicate listeners or execute template scripts.
- Use kebab-case filenames/routes, camelCase JavaScript names, PascalCase future PHP classes, and snake_case database columns.
- Escape user-generated strings before inserting HTML. Use native required/type/range validation plus business checks. Confirm destructive actions and preserve exercise references.
- Mock role checks demonstrate UX only. Do not describe them as secure authentication; never store real passwords in frontend data. Public demo passwords are fictional UI fixtures.
- Keep all backend functionality for later milestones. Future routes call services; services call DAOs; DAOs use PDO prepared statements. All future frontend/backend communication uses AJAX.
- Retain backend/logs/.gitkeep while ignoring generated log files. Never commit secrets, vendor or node_modules.
- Work on milestone1 only for this submission; do not commit, push, switch to main or merge without explicit user approval.
- Run appropriate syntax and workflow checks; record actual results and limitations in docs/TESTING.md and AI usage in docs/AI_LOG.md.
