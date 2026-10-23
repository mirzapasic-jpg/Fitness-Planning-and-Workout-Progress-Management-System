/* Run: node tests/frontend.cjs. Requires Playwright in FORMA_PLAYWRIGHT_PATH,
   or installed locally. Start an HTTP server on 127.0.0.1:8765 first. */
const assert = require("node:assert/strict");
const path = require("node:path");
const { chromium } = require(process.env.FORMA_PLAYWRIGHT_PATH || "playwright");
(async () => {
  const browser = await chromium.launch({ channel: "msedge", headless: true });
  const page = await browser.newPage({
    viewport: { width: 1440, height: 1000 },
  });
  const errors = [],
    failed = [];
  page.on("pageerror", (e) => errors.push(e.message));
  page.on("response", (r) => {
    if (r.status() >= 400) failed.push(`${r.status()} ${r.url()}`);
  });
  let checks = 0;
  const check = (value, message) => {
    assert.ok(value, message);
    checks++;
    console.log("PASS", message);
  };
  const go = async (hash, id) => {
    await page.evaluate((h) => (location.hash = h), hash);
    await page.waitForSelector(id);
  };
  const clickAction = async (action, type) =>
    page
      .locator(`[data-action="${action}"][data-type="${type}"]`)
      .first()
      .click();
  const save = async () => {
    await page.locator("#editorForm button[type=submit]").click();
    await page.waitForFunction(() => !document.querySelector("#editor").open);
  };
  const fill = async (name, value) =>
    page.locator(`[name="${name}"]:visible`).fill(String(value));
  const acceptDelete = async (row) => {
    page.once("dialog", (d) => d.accept());
    await row.locator("[data-action=delete]").click();
  };
  const rowText = async (id, text) =>
    page.locator(`#${id} tbody tr`).filter({ hasText: text });
  await page.goto(process.env.FORMA_BASE_URL || "http://127.0.0.1:8765/");
  await page.waitForSelector("#dashboardStats .stat");
  check(
    await page
      .locator("#dashboardStats .stat strong")
      .allTextContents()
      .then((x) => x.join("|") === "4|17|8,010 kg|3"),
    "dashboard derives 4 workouts, 17 sets, 8010 kg and 3 plans",
  );
  await page.evaluate(() => (window.navigationMarker = 42));
  await go("exercises", "#exerciseList");
  check(
    (await page.locator("#exerciseList tbody tr").count()) === 7,
    "seven exercise fixtures render",
  );
  await page.locator("#exerciseSearch").fill("press");
  check(
    (await page.locator("#exerciseList tbody tr").count()) === 2,
    "exercise search",
  );
  await page.locator("#muscleFilter").selectOption("Chest");
  check(
    (await page.locator("#exerciseList tbody tr").count()) === 1,
    "combined search and muscle filter",
  );
  await page.locator("#exerciseSearch").fill("zzzzz");
  check(
    await page
      .locator("#exerciseList")
      .textContent()
      .then((x) => x.includes("Nothing here")),
    "empty search state",
  );
  await page.locator("#exerciseSearch").fill("");
  await page.locator("#muscleFilter").selectOption("");
  await clickAction("add", "exercise");
  await fill("name", "   ");
  await page.locator("#editorForm button[type=submit]").click();
  check(
    await page
      .locator("#formError")
      .textContent()
      .then((t) => t.includes("at least two")),
    "whitespace exercise rejected",
  );
  await fill("name", "Bench Press");
  await page.locator("#editorForm button[type=submit]").click();
  check(
    await page
      .locator("#formError")
      .textContent()
      .then((t) => t.includes("already exists")),
    "duplicate exercise rejected",
  );
  await fill("name", "Test Row");
  await page.locator("[name=muscle]").selectOption("Back");
  await save();
  let row = await rowText("exerciseList", "Test Row");
  check((await row.count()) === 1, "exercise created");
  await row.locator("[data-action=edit]").click();
  await fill("name", "Test Row Updated");
  await save();
  check(
    (await (await rowText("exerciseList", "Test Row Updated")).count()) === 1,
    "exercise updated",
  );
  await (
    await rowText("exerciseList", "Bench Press")
  )
    .locator("[data-action=delete]")
    .click();
  check(
    await page
      .locator("#notice")
      .textContent()
      .then((x) => x.includes("used in a plan")),
    "referenced exercise deletion protected",
  );
  await go("workout-plans", "#planList");
  await clickAction("add", "plan");
  await fill("name", "Test Plan");
  await page.locator("#editorForm button[type=submit]").click();
  check(
    await page
      .locator("#formError")
      .textContent()
      .then((x) => x.includes("at least one exercise")),
    "plan requires exercise assignment",
  );
  await page
    .locator("fieldset label")
    .filter({ hasText: "Test Row Updated" })
    .locator("input")
    .check();
  await save();
  let plan = page.locator("#planList article").filter({ hasText: "Test Plan" });
  check((await plan.count()) === 1, "plan created");
  await plan.locator("[data-action=view]").click();
  check(
    await page
      .locator("#fields")
      .textContent()
      .then((x) => x.includes("Test Row Updated")),
    "plan view shows assigned exercises",
  );
  await page.locator("[data-close]").first().click();
  await plan.locator("[data-action=edit]").click();
  await fill("name", "Updated Plan");
  await save();
  check(
    await page
      .locator("#planList")
      .textContent()
      .then((x) => x.includes("Updated Plan")),
    "plan updated",
  );
  await go("workout-log", "#logTable");
  await clickAction("add", "log");
  await page
    .locator("[name=exerciseId]")
    .selectOption({ label: "Test Row Updated" });
  await fill("sets", 2);
  await fill("reps", 10);
  await fill("weight", 20);
  await save();
  check(
    (await page.locator("#logTable tbody tr").count()) === 6,
    "workout record created from library",
  );
  row = await rowText("logTable", "Test Row Updated");
  await row.locator("[data-action=edit]").click();
  await fill("weight", 25);
  await save();
  check(
    await row.textContent().then((x) => x.includes("25 kg")),
    "workout record updated",
  );
  await go("progress", "#progressStats");
  check(
    await page
      .locator("#progressStats .stat strong")
      .allTextContents()
      .then((x) => x[1] === "19" && x[2] === "8,510 kg"),
    "progress recomputed from edited log",
  );
  check(
    await page
      .locator("#activityChart")
      .getAttribute("aria-label")
      .then((x) => x.includes("sets")),
    "activity chart data accessible",
  );
  await page
    .locator("#chartExercise")
    .selectOption({ label: "Test Row Updated" });
  await page.locator("#strengthChart").focus();
  await page.keyboard.press("ArrowRight");
  check(
    await page
      .locator("#strengthChartDetails")
      .textContent()
      .then((x) => x.includes("25 kg")),
    "interactive strength chart selection and keyboard",
  );
  check(
    await page
      .locator("#personalRecords")
      .textContent()
      .then((x) => x.includes("25 kg")),
    "personal records from shared logs",
  );
  await go("workout-log", "#logTable");
  await acceptDelete(await rowText("logTable", "Test Row Updated"));
  check(
    (await page.locator("#logTable tbody tr").count()) === 5,
    "workout deletion",
  );
  await go("workout-plans", "#planList");
  plan = page.locator("#planList article").filter({ hasText: "Updated Plan" });
  await acceptDelete(plan);
  check(
    (await page.locator("#planList article").count()) === 3,
    "plan deletion",
  );
  await go("exercises", "#exerciseList");
  row = await rowText("exerciseList", "Test Row Updated");
  page.once("dialog", (d) => d.dismiss());
  await row.locator("[data-action=delete]").click();
  check((await row.count()) === 1, "delete cancellation preserves exercise");
  await acceptDelete(row);
  check(
    (await page.locator("#exerciseList tbody tr").count()) === 7,
    "exercise deletion after references removed",
  );
  await go("profile", "#profileData");
  await page.locator("[data-action=profile]").click();
  await fill("name", "Cancelled Name");
  await page.locator("[data-close]").first().click();
  check(
    await page
      .locator("#profileData")
      .textContent()
      .then((x) => !x.includes("Cancelled Name")),
    "profile cancel",
  );
  await page.locator("[data-action=profile]").click();
  await fill("goal", "Run a marathon");
  await save();
  check(
    await page
      .locator("#profileData")
      .textContent()
      .then((x) => x.includes("Run a marathon")),
    "profile save",
  );
  check(
    (await page.locator('nav a[href="#admin"]').count()) === 0,
    "admin navigation hidden for User",
  );
  await go("admin", "#loginForm");
  check(
    (await page.locator("#loginForm").count()) === 1,
    "direct admin route denied for User",
  );
  await fill("email", "admin@forma.demo");
  await fill("password", "wrong");
  await page.locator("#loginForm button").click();
  check(
    await page
      .locator("#authError")
      .textContent()
      .then((x) => x.includes("demo account")),
    "incorrect demo login rejected",
  );
  await fill("password", "Demo123!");
  await page.locator("#loginForm button").click();
  await page.waitForSelector("#dashboardStats");
  await go("admin", "#usersTable");
  check(
    (await page.locator("#usersTable tbody tr").count()) === 2,
    "Admin login and user list",
  );
  await clickAction("add", "user");
  await fill("name", "Test User");
  await fill("email", "test@forma.demo");
  await save();
  check(
    (await page.locator("#usersTable tbody tr").count()) === 3,
    "admin creates user",
  );
  row = await rowText("usersTable", "Test User");
  await row.locator("[data-action=edit]").click();
  await fill("name", "Updated User");
  await save();
  check(
    (await (await rowText("usersTable", "Updated User")).count()) === 1,
    "admin edits user",
  );
  await acceptDelete(await rowText("usersTable", "Updated User"));
  check(
    (await page.locator("#usersTable tbody tr").count()) === 2,
    "admin deletes user",
  );
  row = await rowText("usersTable", "Sam Taylor");
  await row.locator("[data-action=delete]").click();
  check(
    await page
      .locator("#notice")
      .textContent()
      .then((x) => x.includes("active account")),
    "admin self-delete protected",
  );
  await row.locator("[data-action=edit]").click();
  await page.locator("[name=role]").selectOption("User");
  await page.locator("#editorForm button[type=submit]").click();
  check(
    await page
      .locator("#formError")
      .textContent()
      .then((x) => x.includes("own Admin role")),
    "admin self-demotion protected",
  );
  await page.locator("[data-close]").first().click();
  await clickAction("add", "exercise");
  await fill("name", "Admin Movement");
  await save();
  check(
    await page
      .locator("#adminExercises")
      .textContent()
      .then((x) => x.includes("Admin Movement")),
    "admin exercise creation",
  );
  await acceptDelete(await rowText("adminExercises", "Admin Movement"));
  await page.locator("[data-action=logout]").click();
  await page.waitForSelector("#loginForm");
  await go("register", "#registerForm");
  await fill("name", "New Athlete");
  await fill("email", "new@forma.demo");
  await fill("password", "Demo123!");
  await fill("confirmation", "Mismatch!");
  await page.locator("#registerForm button").click();
  check(
    await page
      .locator("#authError")
      .textContent()
      .then((x) => x.includes("do not match")),
    "password confirmation validation",
  );
  await fill("confirmation", "Demo123!");
  await page.locator("#registerForm button").click();
  await page.waitForSelector("#dashboardStats");
  check(
    await page
      .locator("#dashboardStats .stat strong")
      .allTextContents()
      .then((x) => x[0] === "0" && x[1] === "0"),
    "registration creates isolated User data",
  );
  await go("progress", "#progressStats");
  check(
    await page
      .locator("#activityChartDetails")
      .textContent()
      .then((x) => x.includes("No workout data")),
    "empty progress charts",
  );
  check(
    await page.evaluate(() => window.navigationMarker === 42),
    "SPA navigation preserves document context",
  );
  for (const width of [390, 768, 1440]) {
    await page.setViewportSize({ width, height: 900 });
    for (const [route, selector] of [
      ["dashboard", "#dashboardStats"],
      ["exercises", "#exerciseList"],
      ["workout-log", "#logTable"],
      ["progress", "#progressStats"],
      ["profile", "#profileData"],
    ]) {
      await go(route, selector);
      check(
        await page.evaluate(
          () => document.documentElement.scrollWidth <= innerWidth,
        ),
        `${route} fits ${width}px viewport`,
      );
    }
  }
  await page.setViewportSize({ width: 390, height: 844 });
  await page.screenshot({
    path: path.join(require("node:os").tmpdir(), "forma-mobile.png"),
    fullPage: true,
  });
  await page.setViewportSize({ width: 1440, height: 1000 });
  await go("dashboard", "#dashboardStats");
  await page.screenshot({
    path: path.join(require("node:os").tmpdir(), "forma-desktop.png"),
    fullPage: true,
  });
  await page.reload();
  await page.waitForSelector("#dashboardStats");
  check(
    await page
      .locator("#account")
      .textContent()
      .then((x) => x.includes("Alex Morgan")),
    "reload restores sample data and user",
  );
  await go("progress", "#progressStats");
  await go("dashboard", "#dashboardStats");
  await go("progress", "#progressStats");
  check(
    await page
      .locator("#activityChart")
      .evaluate((c) => c.width > 0 && c.height > 0),
    "charts reinitialize on repeated SPA entry",
  );
  // Native constraints are tested through validity, rather than bypassing browser submission.
  await go("workout-log", "#logTable");
  await clickAction("add", "log");
  await fill("sets", 0);
  check(
    await page.locator("[name=sets]").evaluate((e) => !e.checkValidity()),
    "zero sets rejected by native validation",
  );
  await fill("sets", 1.5);
  check(
    await page.locator("[name=sets]").evaluate((e) => !e.checkValidity()),
    "fractional sets rejected",
  );
  await fill("weight", -1);
  check(
    await page.locator("[name=weight]").evaluate((e) => !e.checkValidity()),
    "negative weight rejected",
  );
  await fill("date", "2099-01-01");
  check(
    await page.locator("[name=date]").evaluate((e) => !e.checkValidity()),
    "future completed date rejected",
  );
  await page.locator("[data-close]").first().click();
  // Confirm every remaining page is responsive, including authentication and admin.
  await page.locator("[data-action=logout]").click();
  await page.waitForSelector("#loginForm");
  await page.setViewportSize({ width: 390, height: 844 });
  check(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
    "login fits 390px viewport",
  );
  await go("register", "#registerForm");
  check(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
    "registration fits 390px viewport",
  );
  await go("login", "#loginForm");
  await fill("email", "admin@forma.demo");
  await fill("password", "Demo123!");
  await page.locator("#loginForm button").click();
  await page.waitForSelector("#dashboardStats");
  await go("admin", "#usersTable");
  check(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
    "admin tables fit 390px viewport",
  );
  await go("workout-plans", "#planList");
  check(
    await page.evaluate(
      () => document.documentElement.scrollWidth <= innerWidth,
    ),
    "plans fit 390px viewport",
  );
  await clickAction("add", "plan");
  check(
    await page
      .locator("#editor")
      .evaluate((e) => e.getBoundingClientRect().width <= innerWidth),
    "editor dialog fits mobile viewport",
  );
  await page.locator("[data-close]").first().click();
  // Template-fetch failure displays a recoverable error and Retry works.
  await page.route("**/views/exercises.html", (route) => route.abort());
  await page.evaluate(() => (location.hash = "exercises"));
  await page.waitForSelector("[data-action=retry]");
  check(
    await page
      .locator("#app")
      .textContent()
      .then((t) => t.includes("Unable to load")),
    "template failure handled gracefully",
  );
  await page.unroute("**/views/exercises.html");
  await page.locator("[data-action=retry]").click();
  await page.waitForSelector("#exerciseList");
  check(
    (await page.locator("#exerciseList tbody tr").count()) === 7,
    "retry restores template",
  );
  // XSS-like strings must remain text.
  await clickAction("add", "exercise");
  await fill("name", "<img src=x onerror=alert(1)>");
  await save();
  check(
    (await page.locator("#exerciseList img").count()) === 0,
    "user input escaped before HTML rendering",
  );
  await acceptDelete(
    await rowText("exerciseList", "<img src=x onerror=alert(1)>"),
  );
  // The expected console error from the deliberately aborted fetch is not a JS exception.
  check(errors.length === 0, `no JavaScript errors: ${errors.join("; ")}`);
  check(failed.length === 0, `no failed HTTP resources: ${failed.join("; ")}`);
  check(
    (
      await page.request.get(new URL("assets/favicon.svg", page.url()).href)
    ).status() === 200,
    "local favicon loads",
  );
  console.log(`RESULT: ${checks} checks passed.`);
  await browser.close();
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
