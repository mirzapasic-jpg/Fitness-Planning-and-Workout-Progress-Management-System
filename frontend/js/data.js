/* Milestone 1: shared, tab-local demonstration data. No backend authentication. */
const FitnessData = (() => {
  const today = () => new Date().toLocaleDateString("en-CA");
  const daysAgo = (n) => {
    const d = new Date();
    d.setDate(d.getDate() - n);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
  };
  const state = {
    users: [
      {
        id: 1,
        name: "Alex Morgan",
        email: "user@forma.demo",
        role: "User",
        age: 24,
        height: 178,
        weight: 76,
        goal: "Build strength",
      },
      {
        id: 2,
        name: "Sam Taylor",
        email: "admin@forma.demo",
        role: "Admin",
        age: 28,
        height: 172,
        weight: 68,
        goal: "Stay active",
      },
    ],
    currentUserId: 1,
    exercises: [
      { id: 1, name: "Bench Press", muscle: "Chest" },
      { id: 2, name: "Squat", muscle: "Legs" },
      { id: 3, name: "Deadlift", muscle: "Back" },
      { id: 4, name: "Shoulder Press", muscle: "Shoulders" },
      { id: 5, name: "Biceps Curl", muscle: "Arms" },
      { id: 6, name: "Lat Pulldown", muscle: "Back" },
      { id: 7, name: "Plank", muscle: "Core" },
    ],
    plans: [
      {
        id: 1,
        userId: 1,
        name: "Push day",
        description: "Build a strong upper body.",
        date: daysAgo(-1),
        exerciseIds: [1, 4, 5],
      },
      {
        id: 2,
        userId: 1,
        name: "Pull day",
        description: "Back and arm strength.",
        date: daysAgo(-3),
        exerciseIds: [3, 6, 5],
      },
      {
        id: 3,
        userId: 1,
        name: "Leg day",
        description: "Your foundation for every lift.",
        date: daysAgo(-5),
        exerciseIds: [2, 7],
      },
    ],
    logs: [
      {
        id: 1,
        userId: 1,
        exerciseId: 1,
        date: daysAgo(6),
        sets: 3,
        reps: 10,
        weight: 50,
      },
      {
        id: 2,
        userId: 1,
        exerciseId: 4,
        date: daysAgo(6),
        sets: 3,
        reps: 10,
        weight: 25,
      },
      {
        id: 3,
        userId: 1,
        exerciseId: 2,
        date: daysAgo(4),
        sets: 4,
        reps: 8,
        weight: 65,
      },
      {
        id: 4,
        userId: 1,
        exerciseId: 3,
        date: daysAgo(2),
        sets: 3,
        reps: 8,
        weight: 80,
      },
      {
        id: 5,
        userId: 1,
        exerciseId: 1,
        date: daysAgo(0),
        sets: 4,
        reps: 8,
        weight: 55,
      },
    ],
  };
  const user = () => state.users.find((u) => u.id === state.currentUserId);
  const logs = () => state.logs.filter((l) => l.userId === state.currentUserId);
  const plans = () =>
    state.plans.filter((p) => p.userId === state.currentUserId);
  const stats = () => {
    const rows = logs();
    return {
      records: rows.length,
      workouts: new Set(rows.map((l) => l.date)).size,
      sets: rows.reduce((n, l) => n + l.sets, 0),
      volume: rows.reduce((n, l) => n + l.sets * l.reps * l.weight, 0),
      recent:
        rows
          .map((l) => l.date)
          .sort()
          .pop() || "No workouts yet",
    };
  };
  return {
    state,
    user,
    logs,
    plans,
    stats,
    today,
    daysAgo,
    nextId: (rows) => Math.max(0, ...rows.map((r) => r.id)) + 1,
  };
})();
