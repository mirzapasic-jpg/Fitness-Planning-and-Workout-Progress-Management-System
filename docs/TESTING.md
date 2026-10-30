# Milestone 1 - Testing Report

## 1. Testing Overview

The Fitness Planning and Workout Progress Management System was tested to verify that the main frontend features work correctly and meet the Milestone 1 requirements.

The application uses HTML, CSS, and JavaScript with sample data. Backend functionality and database integration will be implemented in later milestones.

## 2. Functionality Testing

The following features were covered by automated browser tests:

- **Navigation:** All nine pages load correctly without refreshing the entire application.
- **Dashboard:** Workout statistics, completed workouts, and training information display correctly.
- **Exercise Library:** Adding, editing, deleting, searching, and filtering exercises.
- **Workout Plans:** Creating, viewing, editing, and deleting workout plans.
- **Workout Log:** Recording exercises, sets, repetitions, weights, and dates.
- **Progress Tracking:** Charts, workout statistics, and progress information.
- **Login and Registration:** Demo login, registration, and form validation.
- **Profile:** Editing and saving user information.
- **Admin Panel:** Demo user management and access restrictions.

## 3. Responsive Design

The application was tested at different screen widths:

- Mobile: 390px
- Tablet: 768px
- Desktop: 1440px

The tested layouts displayed correctly without page-level horizontal overflow. Wide tables use horizontal scrolling where necessary.

## 4. Validation and Error Handling

The following scenarios were tested:

- Empty or invalid form inputs.
- Duplicate exercise names.
- Invalid workout values.
- Incorrect login credentials.
- Registration password mismatch.
- Unauthorized access to the admin panel.
- Errors when loading a page.
- Invalid exercise deletion attempts.

## 5. Test Results

| Test | Result |
|---|---|
| JavaScript syntax checks | Passed |
| Automated browser checks | 72 passed |
| SPA navigation | Passed |
| Forms and sample-data operations | Passed |
| Responsive layout checks | Passed |
| Error handling | Passed |
| Git branch verification | Passed |

The automated tests were run using Microsoft Edge and Playwright. No uncaught JavaScript errors were reported.

## 6. Remaining Manual Verification

Before final submission:

- Open the application through XAMPP and check the main features.
- Verify the ER diagram and its relationships.
- Review the documentation and sample accounts.
- Verify the GitHub branch and create the required pull request.

## 7. Milestone 1 Requirements

| Requirement | Status |
|---|---|
| Project folder structure | Complete |
| Static single-page application | Complete |
| Responsive frontend | Complete |
| Login and registration interfaces | Complete |
| Relevant application pages | Complete |
| Database planning (6 entities) | Complete |
| Draft ER diagram | Complete |
| External API feature planned | Complete |
| README and required documentation | Complete |

## Conclusion

Based on the recorded automated tests, the application meets the implementation requirements for Milestone 1.

The frontend currently uses temporary data. Real authentication, PHP backend functionality, MySQL database integration, and external API integration are planned for future milestones.

Final manual verification and GitHub submission are still required.
## Milestone 2 — 2026-10-30

The report above records M1 results. M2 adds independent database/DAO verification; the M1 browser suite was not rerun and the frontend was not changed.

Environment: Windows / XAMPP PHP 8.2.12, PDO with pdo_mysql, MariaDB 10.4.32 at 127.0.0.1. The target fitness_planner database did not exist before the schema import. Importing backend/database/fitness-planner.sql using the XAMPP mysql client succeeded without SQL errors.

| Check | Actual result |
| --- | --- |
| PHP syntax: configuration, Database.php, BaseDao, all six entity DAOs and tests/dao.php | All passed |
| PDO connection and sample reads from all six tables | Passed |
| Create, read by ID, persisted update, unchanged update, delete for all six DAOs | Passed |
| Missing record behavior, empty writes, primary-key protection, invalid column rejection | Passed |
| SQL-like value stored literally without changing role | Passed |
| Duplicate emails, exercise names, plan assignments/positions, user/date sessions | Rejected as expected |
| Lowercase/trimmed email CHECK | Rejected uppercase fixture as expected |
| All seven foreign keys and an invalid FK update | Rejected invalid references as expected |
| Positive sets/reps/position and nonnegative target/log weights | Invalid values rejected as expected |
| Exercise delete restricted with assignment-only or log-only references | Passed |
| Plan cascade + session SET NULL while retaining logs | Passed |
| Session cascade and user cascade through all owned children | Passed |
| Shared exercises survive user deletion | Passed |
| Original full row snapshots compared after rollback | Identical; passed |
| CLI runtime suite | 98 checks passed |

Run from the repository root: `C:\xampp\php\php.exe tests/dao.php`. The script returns exit code 1 on failure and rolls back an active test transaction; success returns 0. It is CLI-only and does not create/import/drop tables. Run with the supplied sample data on an idle local database. InnoDB auto-increment counters can advance despite rollback; no test rows remain.

Limitations: Oracle MySQL and phpMyAdmin's GUI import were not exercised. Future service-level date, ownership, email-format and required-plan-assignment validation are intentionally outside M2. Authentication, HTTP routes/status handling, frontend AJAX database integration and production deployment remain later milestones. No claims of those checks passing are made here.
