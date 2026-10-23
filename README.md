# Fitness Planning and Workout Progress Management System

Forma is a responsive single-page fitness dashboard for planning routines, managing an exercise library, recording training, and exploring progress.

## Milestone 1

Implemented: nine HTML views loaded through AJAX without full document reloads; workout plan, exercise and workout log CRUD; dashboard; interactive canvas charts; mock login/registration with User and Admin roles; editable profile; admin user/exercise management; and a six-entity draft ERD. Data is shared between pages, held in memory, and reset on reload. The initial demo is signed in as the sample User.

This is a frontend demonstration. Client-side roles are not security controls. There is no PHP API, database, JWT authentication, external API integration, OpenAPI page, or deployed application yet.

## Run locally with XAMPP

1. Place the repository under `C:\xampp\htdocs\MirzaPasic\Fitness-Planning-and-Workout-Progress-Management-System`.
2. Start Apache from the XAMPP Control Panel; MySQL is not needed for Milestone 1.
3. Open `http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/frontend/`.
4. Navigate using the sidebar. Direct hash links such as `frontend/#progress` also work. Do not open `index.html` with `file://`; templates require HTTP fetch.

No build step, CDN, package installation, configuration file, or SQL import is required for this milestone. JavaScript and HTML Canvas must be enabled in a modern browser.

## Roles and demo accounts

| Role | Email | Public demo password |
| --- | --- | --- |
| User | user@forma.demo | Demo123! |
| Admin | admin@forma.demo | Demo123! |

Log out to switch accounts. Registration creates a User. Admin-created and registered accounts use the same public demo password. Passwords are not stored. Use fictional data. These values are public UI fixtures, not credentials for any real service.

Users manage their own plans and workout records, edit their profile, and manage the shared exercise library. Admins also manage users and shared exercises. Deleting a user deletes their mock plans/logs. The active admin cannot delete or demote themselves. Referenced exercises must be removed from plans and logs before deletion.

## Features and definitions

- Plans: name, description, scheduled date, and selected exercise assignments; view/edit/delete with confirmation.
- Exercise library: realistic sample movements, combined name search and muscle filter, validated add/edit/delete.
- Workout history: exercise, date, integer sets/repetitions, and weight in kg; completed dates cannot be in the future.
- Progress: one completed workout means one distinct training date. Total volume is sets × reps × external weight. Bodyweight/timed entries have zero external-load volume; use repetitions as seconds for timed exercises. Personal records show best external weight and best set volume.
- Charts: sets per date and exercise-specific maximum weight per date; hover/tap or focus and use arrow keys to inspect values. Charts redraw on SPA entry, selection, and resize. Accessible text describes the data.
- Dashboard: totals and recent activity derive from the current user's shared log; upcoming plans derive from schedule dates.
- All mutations are tab-local. Reload restores initial fixtures; logout keeps demo data in the same tab.

## Technology and structure

Current: HTML templates, CSS, vanilla JavaScript, Fetch API for SPA templates, native dialog and canvas. Planned: PHP with FlightPHP REST routes, MySQL via PDO, JWT, OpenAPI, request/error middleware and logging.

```text
frontend/
  index.html
  css/style.css
  js/data.js          shared fixtures and derived statistics
  js/app.js           router, renderers, event handling and dialogs
  views/              nine HTML templates
  assets/             local favicon and reserved static assets
backend/
  routes/ services/ dao/ middleware/ config/ logs/
docs/
  ERD.html            standalone viewable draft diagram
  ERD.md              schema, relationships and design choices
  AI_LOG.md
  TESTING.md
AGENTS.md
.gitignore
README.md
```

## Database planning

See [draft ERD](docs/ERD.html) in a browser and [schema notes](docs/ERD.md). Six entities: Users, Exercises, Workout Plans, Plan Exercises, Workout Sessions, Exercise Logs. Users own plans and sessions; plans have many exercise assignments; sessions have many logs; exercises are referenced by assignments and logs. Optional session-to-plan association preserves history when a plan is deleted. The mock frontend groups records by user/date; database sessions will provide explicit session IDs later.

No database has been created. SQL import and secret configuration instructions will accompany Milestone 2. Secrets will live in ignored configuration, with a non-secret example committed later.

## Planned external API

The dashboard reserves a visible **Fuel your recovery** section for TheMealDB recipe discovery: meal cards, ingredients, and recipe details relevant to post-workout meals. Milestone 3 will add a FlightPHP service/endpoint with timeout and friendly failure handling, configuration for any required key, error logging, and OpenAPI documentation. Live frontend integration follows in Milestone 4. No external request is made now.

## Later milestones

- M2: MySQL schema/sample export and PDO DAO CRUD.
- M3: services, FlightPHP REST CRUD, OpenAPI/Swagger, third-party API service.
- M4: hashed passwords, JWT, reusable role middleware, request/error logging, fully connected AJAX frontend.
- M5: frontend MVC refinement, full client/server validation and deployment.

Swagger URL: not available yet. Deployment URL: not available yet.

