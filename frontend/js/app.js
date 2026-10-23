/* HTML templates are fetched once per navigation; all data is a disposable demo. */
const D = FitnessData,
  S = D.state;
const pages = {
  dashboard: "Dashboard",
  "workout-plans": "Workout plans",
  exercises: "Exercise library",
  "workout-log": "Workout log",
  progress: "Progress",
  profile: "My profile",
  admin: "Admin",
  login: "Log in",
  register: "Register",
};
const esc = (value) =>
  String(value ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        c
      ],
  );
const $ = (id) => document.getElementById(id);
const exerciseName = (id) =>
  S.exercises.find((e) => e.id === id)?.name || "Removed exercise";
let currentPage = "",
  navigationId = 0,
  editing = null,
  noticeTimer;
function notify(message) {
  $("notice").textContent = message;
  $("notice").style.display = "block";
  clearTimeout(noticeTimer);
  noticeTimer = setTimeout(() => ($("notice").style.display = "none"), 4200);
}
function navigation() {
  document.querySelector("nav").innerHTML = Object.entries(pages)
    .filter(([p]) =>
      p === "admin"
        ? D.user()?.role === "Admin"
        : !["login", "register"].includes(p),
    )
    .map(
      ([p, n]) =>
        `<a href="#${p}" ${p === currentPage ? 'class="active" aria-current="page"' : ""}>${n}</a>`,
    )
    .join("");
  $("account").innerHTML = D.user()
    ? `<span>${esc(D.user().name)} · ${esc(D.user().role)}</span><button class="secondary" data-action="logout">Log out</button>`
    : '<a href="#login">Log in</a><a href="#register">Register</a>';
}
async function showPage(page) {
  if (location.hash !== `#${page}`) {
    location.hash = page;
    return;
  }
  await loadPage(page);
}
async function loadPage(page) {
  const token = ++navigationId;
  if (!Object.hasOwn(pages, page)) {
    page = "dashboard";
    notify("Unknown page. Showing dashboard.");
  }
  if (page === "admin" && D.user()?.role !== "Admin") {
    notify("Admin access requires the Admin demo role.");
    location.hash = "login";
    return;
  }
  if (!D.user() && !["login", "register"].includes(page)) {
    location.hash = "login";
    return;
  }
  if ($("editor").open) $("editor").close();
  currentPage = page;
  navigation();
  $("app").setAttribute("aria-busy", "true");
  try {
    const response = await fetch(`views/${page}.html`);
    if (!response.ok) throw Error(`HTTP ${response.status}`);
    const html = await response.text();
    if (token !== navigationId) return;
    $("app").innerHTML = html;
    renderPage();
    $("app").focus();
  } catch (error) {
    if (token !== navigationId) return;
    $("app").innerHTML =
      '<section class="card"><h1>Unable to load this page</h1><p>Run the frontend through XAMPP or a local HTTP server.</p><button data-action="retry">Try again</button></section>';
    console.error(error);
  } finally {
    if (token === navigationId) $("app").removeAttribute("aria-busy");
  }
}
const statCards = (items) =>
  `<div class="stats">${items.map(([title, value]) => `<div class="stat"><small>${esc(title)}</small><strong>${esc(value)}</strong></div>`).join("")}</div>`;
const actions = (type, id, view = false) =>
  `${view ? `<button data-action="view" data-type="${type}" data-id="${id}">View</button>` : ""}<button class="secondary" data-action="edit" data-type="${type}" data-id="${id}">Edit</button><button class="danger" data-action="delete" data-type="${type}" data-id="${id}">Delete</button>`;
function table(headers, rows) {
  return rows.length
    ? `<div class="table-wrap"><table><thead><tr>${headers.map((h) => `<th scope="col">${h}</th>`).join("")}</tr></thead><tbody>${rows.join("")}</tbody></table></div>`
    : '<p class="empty">Nothing here yet. Add your first entry to get started.</p>';
}
function logTable(rows, editable = true) {
  return table(
    [
      "Date",
      "Exercise",
      "Sets × reps",
      "Weight",
      ...(editable ? ["Actions"] : []),
    ],
    rows
      .slice()
      .sort((a, b) => b.date.localeCompare(a.date) || b.id - a.id)
      .map(
        (l) =>
          `<tr><td>${esc(l.date)}</td><td>${esc(exerciseName(l.exerciseId))}</td><td>${l.sets} × ${l.reps}</td><td>${l.weight} kg</td>${editable ? `<td>${actions("log", l.id)}</td>` : ""}</tr>`,
      ),
  );
}
function renderPage() {
  const st = D.stats();
  if (currentPage === "dashboard") {
    $("greeting").textContent =
      `Let's get stronger, ${D.user().name.split(" ")[0]}.`;
    $("dashboardStats").innerHTML = statCards([
      ["Completed workouts", st.workouts],
      ["Total sets", st.sets],
      ["Training volume", `${st.volume.toLocaleString()} kg`],
      ["Active plans", D.plans().length],
    ]);
    $("recentActivity").innerHTML = logTable(
      D.logs()
        .slice()
        .sort((a, b) => b.date.localeCompare(a.date))
        .slice(0, 4),
      false,
    );
    $("upcomingPlans").innerHTML =
      D.plans()
        .filter((p) => p.date >= D.today())
        .sort((a, b) => a.date.localeCompare(b.date))
        .map(
          (p) =>
            `<p><strong>${esc(p.name)}</strong><br><span class="muted">${esc(p.date)} · ${p.exerciseIds.length} exercises</span></p>`,
        )
        .join("") || "<p>No upcoming plans. Schedule one in Workout plans.</p>";
  }
  if (currentPage === "exercises") renderExercises();
  if (currentPage === "workout-plans") {
    $("planList").innerHTML =
      D.plans()
        .map(
          (p) =>
            `<article class="card"><span class="eyebrow">${esc(p.date || "Unscheduled")}</span><h2>${esc(p.name)}</h2><p class="muted">${esc(p.description)}</p><p>${p.exerciseIds.length} exercises</p><div class="actions">${actions("plan", p.id, true)}</div></article>`,
        )
        .join("") ||
      '<p class="empty">No plans yet. Create your first routine.</p>';
  }
  if (currentPage === "workout-log") {
    $("logStats").innerHTML = statCards([
      ["Logged exercises", st.records],
      ["Total sets", st.sets],
      ["Completed workouts", st.workouts],
      ["Most recent", st.recent],
    ]);
    $("logTable").innerHTML = logTable(D.logs());
  }
  if (currentPage === "progress") {
    $("progressStats").innerHTML = statCards([
      ["Completed workouts", st.workouts],
      ["Total sets", st.sets],
      ["Training volume", `${st.volume.toLocaleString()} kg`],
      ["Logged exercises", st.records],
    ]);
    $("chartExercise").innerHTML = S.exercises
      .map((e) => `<option value="${e.id}">${esc(e.name)}</option>`)
      .join("");
    renderCharts();
    $("personalRecords").innerHTML = table(
      ["Exercise", "Best weight", "Best set volume"],
      S.exercises
        .filter((e) => D.logs().some((l) => l.exerciseId === e.id))
        .map((e) => {
          const logs = D.logs().filter((l) => l.exerciseId === e.id);
          return `<tr><td>${esc(e.name)}</td><td>${Math.max(...logs.map((l) => l.weight))} kg</td><td>${Math.max(...logs.map((l) => l.reps * l.weight))} kg</td></tr>`;
        }),
    );
  }
  if (currentPage === "profile") {
    $("profileData").innerHTML = `<dl class="profile-details">${[
      ["Name", D.user().name],
      ["Email", D.user().email],
      ["Age", D.user().age || "Not set"],
      ["Height", D.user().height ? D.user().height + " cm" : "Not set"],
      ["Weight", D.user().weight ? D.user().weight + " kg" : "Not set"],
      ["Fitness goal", D.user().goal || "Not set"],
    ]
      .map(([k, v]) => `<div><dt>${k}</dt><dd>${esc(v)}</dd></div>`)
      .join("")}</dl>`;
  }
  if (currentPage === "admin") {
    if (D.user()?.role !== "Admin") return;
    $("adminStats").innerHTML = statCards([
      ["Users", S.users.length],
      ["Exercises", S.exercises.length],
      ["Plans", S.plans.length],
      ["Workout records", S.logs.length],
    ]);
    $("usersTable").innerHTML = table(
      ["Name", "Email", "Role", "Actions"],
      S.users.map(
        (u) =>
          `<tr><td>${esc(u.name)}</td><td>${esc(u.email)}</td><td><span class="tag">${u.role}</span></td><td>${actions("user", u.id)}</td></tr>`,
      ),
    );
    $("adminExercises").innerHTML = table(
      ["Exercise", "Muscle", "Actions"],
      S.exercises.map(
        (e) =>
          `<tr><td>${esc(e.name)}</td><td>${esc(e.muscle)}</td><td>${actions("exercise", e.id)}</td></tr>`,
      ),
    );
  }
}
function renderExercises() {
  const q = $("exerciseSearch").value.toLowerCase().trim(),
    muscle = $("muscleFilter").value;
  const rows = S.exercises.filter(
    (e) => e.name.toLowerCase().includes(q) && (!muscle || e.muscle === muscle),
  );
  $("exerciseList").innerHTML = table(
    ["Exercise", "Muscle group", "Actions"],
    rows.map(
      (e) =>
        `<tr><td><strong>${esc(e.name)}</strong></td><td><span class="tag">${esc(e.muscle)}</span></td><td>${actions("exercise", e.id)}</td></tr>`,
    ),
  );
  $("exerciseCount").textContent = `${rows.length} exercises`;
}
const input = (name, label, value = "", type = "text", attributes = "") =>
  `<label for="field-${name}">${label}</label><input id="field-${name}" name="${name}" type="${type}" value="${esc(value)}" ${attributes}>`;
const select = (name, label, options, value) =>
  `<label for="field-${name}">${label}</label><select id="field-${name}" name="${name}">${options.map(([id, title]) => `<option value="${esc(id)}" ${String(value) === String(id) ? "selected" : ""}>${esc(title)}</option>`).join("")}</select>`;
function collection(type) {
  return {
    exercise: S.exercises,
    plan: D.plans(),
    log: D.logs(),
    user: S.users,
    profile: [D.user()],
  }[type];
}
function openEditor(type, id) {
  if (type === "user" && D.user()?.role !== "Admin") return;
  const row = id ? collection(type)?.find((r) => r.id === Number(id)) : null;
  if (id && !row) return;
  editing = { type, id: row?.id };
  let fields = "";
  $("editorTitle").textContent =
    `${row ? "Edit" : "Add"} ${{ exercise: "exercise", plan: "workout plan", log: "workout record", user: "user", profile: "profile" }[type]}`;
  if (type === "exercise")
    fields =
      input(
        "name",
        "Exercise name",
        row?.name,
        "text",
        'required minlength="2" maxlength="80"',
      ) +
      select(
        "muscle",
        "Muscle group",
        ["Chest", "Back", "Legs", "Shoulders", "Arms", "Core"].map((x) => [
          x,
          x,
        ]),
        row?.muscle,
      );
  if (type === "plan")
    fields =
      input(
        "name",
        "Plan name",
        row?.name,
        "text",
        'required minlength="2" maxlength="80"',
      ) +
      input(
        "description",
        "Description",
        row?.description,
        "text",
        'maxlength="240"',
      ) +
      input(
        "date",
        "Planned date",
        row?.date || D.today(),
        "date",
        "required",
      ) +
      `<fieldset><legend>Assigned exercises (select at least one)</legend>${S.exercises.map((e) => `<label><input type="checkbox" name="exerciseIds" value="${e.id}" ${row?.exerciseIds.includes(e.id) ? "checked" : ""}>${esc(e.name)} · ${esc(e.muscle)}</label>`).join("")}</fieldset>`;
  if (type === "log") {
    if (!S.exercises.length) {
      notify("Add an exercise before logging a workout.");
      return;
    }
    fields =
      select(
        "exerciseId",
        "Exercise",
        S.exercises.map((e) => [e.id, e.name]),
        row?.exerciseId,
      ) +
      input(
        "date",
        "Workout date",
        row?.date || D.today(),
        "date",
        `required max="${D.today()}"`,
      ) +
      input(
        "sets",
        "Sets",
        row?.sets || 3,
        "number",
        'required min="1" max="100" step="1"',
      ) +
      input(
        "reps",
        "Repetitions (seconds for timed exercises)",
        row?.reps || 10,
        "number",
        'required min="1" max="1000" step="1"',
      ) +
      input(
        "weight",
        "Weight (kg; 0 for bodyweight)",
        row?.weight ?? 0,
        "number",
        'required min="0" max="1000" step="0.5"',
      );
  }
  if (["user", "profile"].includes(type)) {
    fields =
      input(
        "name",
        "Full name",
        row?.name,
        "text",
        'required minlength="2" maxlength="80"',
      ) +
      input("email", "Email", row?.email, "email", 'required maxlength="120"');
    if (type === "user")
      fields += select(
        "role",
        "Demo role",
        [
          ["User", "User"],
          ["Admin", "Admin"],
        ],
        row?.role || "User",
      );
    if (type === "profile")
      fields +=
        input(
          "age",
          "Age",
          row?.age,
          "number",
          'required min="13" max="120" step="1"',
        ) +
        input(
          "height",
          "Height (cm)",
          row?.height,
          "number",
          'required min="80" max="250" step="1"',
        ) +
        input(
          "weight",
          "Weight (kg)",
          row?.weight,
          "number",
          'required min="25" max="400" step="0.1"',
        ) +
        input(
          "goal",
          "Fitness goal",
          row?.goal,
          "text",
          'required minlength="2" maxlength="120"',
        );
  }
  $("fields").innerHTML = fields;
  $("formError").textContent = "";
  $("editor").showModal();
}
function saveEditor(event) {
  event.preventDefault();
  const { type, id } = editing;
  if (type === "user" && D.user()?.role !== "Admin") return;
  const f = new FormData(event.target),
    values = Object.fromEntries(f);
  for (const k in values) values[k] = values[k].trim();
  const error = (m) => {
    $("formError").textContent = m;
  };
  if (Object.hasOwn(values, "name") && values.name.length < 2)
    return error("Please enter a name with at least two characters.");
  let row = collection(type)?.find((r) => r.id === id);
  if (id && !row) return error("This record is no longer available.");
  let data;
  if (type === "exercise") {
    if (
      S.exercises.some(
        (e) =>
          e.id !== id && e.name.toLowerCase() === values.name.toLowerCase(),
      )
    )
      return error("An exercise with this name already exists.");
    data = { name: values.name, muscle: values.muscle };
  }
  if (type === "plan") {
    const ids = f.getAll("exerciseIds").map(Number);
    if (!ids.length) return error("Select at least one exercise.");
    data = {
      name: values.name,
      description: values.description,
      date: values.date,
      exerciseIds: ids,
      userId: S.currentUserId,
    };
  }
  if (type === "log") {
    if (values.date > D.today())
      return error("Completed workouts cannot have a future date.");
    data = {
      exerciseId: Number(values.exerciseId),
      date: values.date,
      sets: Number(values.sets),
      reps: Number(values.reps),
      weight: Number(values.weight),
      userId: S.currentUserId,
    };
  }
  if (["user", "profile"].includes(type)) {
    values.email = values.email.toLowerCase();
    if (
      S.users.some((u) => u.id !== id && u.email.toLowerCase() === values.email)
    )
      return error("This email is already in use.");
    if (
      type === "user" &&
      row?.id === S.currentUserId &&
      values.role !== "Admin"
    )
      return error(
        "You cannot remove your own Admin role. Log in as another admin first.",
      );
    data = {
      name: values.name,
      email: values.email,
      ...(type === "user"
        ? { role: values.role }
        : {
            age: Number(values.age),
            height: Number(values.height),
            weight: Number(values.weight),
            goal: values.goal,
          }),
    };
  }
  if (row) Object.assign(row, data);
  else {
    const target = {
      exercise: S.exercises,
      plan: S.plans,
      log: S.logs,
      user: S.users,
    }[type];
    target.push({ id: D.nextId(target), ...data });
  }
  $("editor").close();
  navigation();
  renderPage();
  notify("Changes saved for this demo session.");
}
function removeRecord(type, id) {
  if (type === "user" && D.user()?.role !== "Admin") return;
  const row = collection(type)?.find((r) => r.id === id);
  if (!row) return;
  if (type === "user" && id === S.currentUserId) {
    notify("You cannot delete your active account.");
    return;
  }
  if (
    type === "exercise" &&
    (S.logs.some((l) => l.exerciseId === id) ||
      S.plans.some((p) => p.exerciseIds.includes(id)))
  ) {
    notify(
      "This exercise is used in a plan or workout log. Remove those references first.",
    );
    return;
  }
  if (
    !confirm(
      `Delete ${row.name || "this workout record"}?${type === "user" ? " Their plans and logs will also be deleted." : ""}`,
    )
  )
    return;
  const key = {
    exercise: "exercises",
    plan: "plans",
    log: "logs",
    user: "users",
  }[type];
  S[key] = S[key].filter((r) => r.id !== id);
  if (type === "user") {
    S.plans = S.plans.filter((p) => p.userId !== id);
    S.logs = S.logs.filter((l) => l.userId !== id);
  }
  renderPage();
  notify("Record deleted.");
}
function renderCharts() {
  if (currentPage !== "progress") return;
  const logs = D.logs(),
    dates = [...new Set(logs.map((l) => l.date))].sort();
  drawChart(
    "activityChart",
    dates.map((date) => ({
      label: date,
      value: logs
        .filter((l) => l.date === date)
        .reduce((n, l) => n + l.sets, 0),
    })),
    "sets",
  );
  const id = Number($("chartExercise").value);
  drawChart(
    "strengthChart",
    dates
      .filter((date) =>
        logs.some((l) => l.date === date && l.exerciseId === id),
      )
      .map((date) => ({
        label: date,
        value: Math.max(
          ...logs
            .filter((l) => l.date === date && l.exerciseId === id)
            .map((l) => l.weight),
        ),
      })),
    "kg",
  );
}
function drawChart(id, points, unit) {
  const canvas = $(id),
    ctx = canvas.getContext("2d"),
    width = canvas.clientWidth || 400,
    height = 230,
    ratio = devicePixelRatio || 1;
  canvas.width = width * ratio;
  canvas.height = height * ratio;
  ctx.scale(ratio, ratio);
  ctx.clearRect(0, 0, width, height);
  const detail = $(id + "Details");
  detail.textContent = points.length
    ? "Hover, tap, or focus a bar for its value."
    : "No workout data yet. Log a workout to see your progress.";
  canvas.setAttribute(
    "aria-label",
    `${unit === "sets" ? "Sets per workout date" : "Best weight over time"}: ${points.map((p) => `${p.label}: ${p.value} ${unit}`).join(", ") || "No data"}`,
  );
  const max = Math.max(1, ...points.map((p) => p.value)),
    left = 40,
    bottom = 192,
    plot = width - left - 12;
  ctx.font = "11px system-ui";
  ctx.fillStyle = "#657773";
  for (let i = 0; i <= 4; i++) {
    const y = bottom - i * 40;
    ctx.fillText(Math.round((max * i) / 4), 0, y + 4);
    ctx.strokeStyle = "#e5ece6";
    ctx.beginPath();
    ctx.moveTo(left, y);
    ctx.lineTo(width, y);
    ctx.stroke();
  }
  const step = plot / Math.max(1, points.length);
  points.forEach((p, i) => {
    const x = left + i * step + step * 0.18,
      h = (p.value / max) * 160;
    ctx.fillStyle = "#388761";
    ctx.fillRect(x, bottom - h, step * 0.64, Math.max(h, 2));
    if (points.length < 9) {
      ctx.save();
      ctx.translate(x, bottom + 18);
      ctx.fillStyle = "#657773";
      ctx.fillText(p.label.slice(5), 0, 0);
      ctx.restore();
    }
  });
  let selected = 0;
  const display = (i) => {
    if (!points.length) return;
    selected = Math.max(0, Math.min(points.length - 1, i));
    detail.textContent = `${points[selected].label}: ${points[selected].value} ${unit}`;
  };
  canvas.onpointermove = (e) => display(Math.floor((e.offsetX - left) / step));
  canvas.onclick = (e) => display(Math.floor((e.offsetX - left) / step));
  canvas.onfocus = () => display(selected);
  canvas.onkeydown = (e) => {
    if (["ArrowLeft", "ArrowRight"].includes(e.key)) {
      e.preventDefault();
      display(selected + (e.key === "ArrowRight" ? 1 : -1));
    }
  };
}
document.addEventListener("click", (event) => {
  const close = event.target.closest("[data-close]");
  if (close) {
    $("editor").close();
    return;
  }
  const b = event.target.closest("[data-action]");
  if (!b) return;
  const { action, type, id } = b.dataset;
  if (action === "add") openEditor(type);
  if (action === "edit") openEditor(type, Number(id));
  if (action === "delete") removeRecord(type, Number(id));
  if (action === "profile") openEditor("profile", S.currentUserId);
  if (action === "retry") loadPage(currentPage);
  if (action === "logout") {
    S.currentUserId = null;
    location.hash = "login";
    navigation();
    notify("Logged out of the demo.");
  }
  if (action === "view") {
    const p = D.plans().find((p) => p.id === Number(id));
    if (!p) return;
    $("editorTitle").textContent = p.name;
    $("fields").innerHTML =
      `<p>${esc(p.description)}</p><p>Planned: ${esc(p.date)}</p><ul class="plan-list">${p.exerciseIds.map((id) => `<li>${esc(exerciseName(id))}</li>`).join("")}</ul>`;
    $("formError").textContent = "";
    $("editorForm").querySelector("[type=submit]").hidden = true;
    $("editor").showModal();
  }
});
$("editor").addEventListener("close", () => {
  $("editorForm").querySelector("[type=submit]").hidden = false;
  editing = null;
});
$("editorForm").addEventListener("submit", saveEditor);
document.addEventListener("input", (e) => {
  if (e.target.id === "exerciseSearch") renderExercises();
});
document.addEventListener("change", (e) => {
  if (e.target.id === "muscleFilter") renderExercises();
  if (e.target.id === "chartExercise") renderCharts();
});
document.addEventListener("submit", (event) => {
  if (!["loginForm", "registerForm"].includes(event.target.id)) return;
  event.preventDefault();
  const f = new FormData(event.target),
    email = String(f.get("email")).trim().toLowerCase(),
    password = String(f.get("password"));
  const error = $("authError");
  error.textContent = "";
  if (event.target.id === "loginForm") {
    const user = S.users.find((u) => u.email.toLowerCase() === email);
    if (!user || password !== "Demo123!") {
      error.textContent =
        "Use a listed demo account (or a registered demo email) and Demo123!";
      return;
    }
    S.currentUserId = user.id;
    notify("Demo login successful.");
    location.hash = "dashboard";
  } else {
    const name = String(f.get("name")).trim();
    if (name.length < 2) {
      error.textContent = "Enter a name with at least two characters.";
      return;
    }
    if (password !== String(f.get("confirmation"))) {
      error.textContent = "Passwords do not match.";
      return;
    }
    if (password !== "Demo123!") {
      error.textContent =
        "For this mock interface, use the public demo password Demo123!";
      return;
    }
    if (S.users.some((u) => u.email.toLowerCase() === email)) {
      error.textContent = "This email is already registered.";
      return;
    }
    const user = {
      id: D.nextId(S.users),
      name,
      email,
      role: "User",
      age: null,
      height: null,
      weight: null,
      goal: "Build strength",
    };
    S.users.push(user);
    S.currentUserId = user.id;
    notify("Demo account created.");
    location.hash = "dashboard";
  }
});
window.addEventListener("hashchange", () =>
  loadPage(location.hash.slice(1) || "dashboard"),
);
let resizeTimer;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(renderCharts, 100);
});
if (!location.hash) location.hash = "dashboard";
else loadPage(location.hash.slice(1));
