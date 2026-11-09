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

## Milestone 3 — 2026-11-07

Environment: Windows XAMPP, Apache with mod_rewrite and AllowOverride All, PHP 8.2.12, local MariaDB development database using the existing ignored configuration, FlightPHP 3.19.5. Apache/MySQL were initially stopped and were started for testing. The database did not need reimporting. The original DAO classes, SQL schema, ERD and frontend files were not edited.

| Check actually performed | Result |
| --- | --- |
| PHP syntax checks for application PHP, example config and CLI tests (private settings not printed) | Passed |
| Existing tests/dao.php | 98 checks passed; rollback restored original rows |
| tests/api.php through the actual Apache/XAMPP backend URL | 346 checks passed; temporary records removed |
| All six entities: list, create, read, persisted/unchanged partial update and delete | Passed; correct 200/201 responses |
| All six entities: missing IDs for GET/PUT/DELETE | 404 JSON as expected |
| Empty/unknown fields, malformed/scalar/array JSON, wrong content type and invalid route IDs | 400 JSON as expected |
| Entity validation: email normalization/format, names/lengths, roles/hash input, dates/future dates, positive integer counts and nonnegative two-decimal weights | Passed |
| All seven foreign keys, invalid reference update, session-plan ownership and linked-plan transfer | Rejected with 400 as expected |
| Duplicate emails/exercise names/assignments/user-date sessions and referenced exercise deletion | Safe 400 responses without SQL details |
| User create/read/list/update responses | Password hashes omitted |
| Live GET /api/meals/recovery | 200 with real TheMealDB recipe names, ingredients and structured fields |
| MealService simulated connection timeout, upstream 500, malformed JSON, null/empty meal lists, malformed meal rows | Friendly 503 exceptions; failures appended to backend/logs/error.log |
| Meal ingredient mapping and missing optional fields | Passed |
| Isolated temporary PHP server using production error mapping and test-only routes outside the repository | HTTP 500 and 503 JSON matched expected safe messages; temporary server stopped |
| External API error log inspected | Timestamped upstream failures and explicitly simulated timeout entries present; log remains ignored |
| OpenAPI validation with temporary @apidevtools/swagger-parser | Passed including local schema references; 32 documented operations |
| Swagger in headless Microsoft Edge | All 32 operations rendered, no parser/JavaScript errors; Try it out returned the actual exercise list |
| Apache access to config.php, SQL schema, error.log, composer.lock and vendor/autoload.php | All denied with 403 |
| Existing frontend JavaScript syntax | Both application scripts passed |
| Existing tests/frontend.cjs, headless Edge through XAMPP | First run reported null-canvas JavaScript error at final error assertion; repeat passed all 72 checks |

The frontend failure appears timing-dependent: the existing resize listener schedules renderCharts after 100 ms and navigation can remove its canvas. No frontend files or browser-test expectations were changed. A passing repeat does not prove this pre-existing race is fixed; it remains a known M1 limitation outside the M3 backend changes.

Commands from the repository root:

```powershell
C:\xampp\php\php.exe tests/dao.php
C:\xampp\php\php.exe tests/api.php
node --check frontend/js/app.js
node --check frontend/js/data.js
```

The existing browser suite used Playwright installed outside the repository in `%TEMP%\forma-m3-checks`, `FORMA_PLAYWRIGHT_PATH` pointing to its node_modules/playwright and `FORMA_BASE_URL` set to `http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/frontend/`. Swagger/parser tools and the isolated HTTP error harness were also temporary, not runtime dependencies.

The API suite compares the complete original JSON collections after cleanup; the DAO suite separately compares raw original rows after rollback. Neither suite modifies sample records or clears tables. Auto-increment counters advanced as expected. API tests deliberately append simulated external failures to the ignored log. An interrupted API test may leave its uniquely named fixtures; only remove those test IDs.

Limitations: no production deployment, Oracle MySQL, real upstream outage or actual 8-second network timeout was forced. Connection failure/timeout handling was simulated at the transport boundary; HTTP 503 formatting was also checked with the isolated server. HTTP 500 used an injected unexpected exception in that server, not an outage of the development database. External-provider availability and Swagger CDN access depend on the network. Authentication/authorization (401/403), middleware/full request logging and frontend API integration are M4 work and were not claimed as implemented or tested. No GitHub or submission actions were performed.

Final package check: Composer validated composer.json/composer.lock. Its only warning was that no project license is specified; no license was invented. Final PHP syntax checks passed after the last validation edits.
