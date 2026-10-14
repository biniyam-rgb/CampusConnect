
import './StudentDashboard.css';

function StudentDashboard() {
  return (
    <div className="student-dashboard">
      <header className="student-header">
        <h1>CampusConnect</h1>
        <button type="button">Logout</button>
      </header>

      <main className="student-main">
        <h2>Student Dashboard</h2>
        <p>Welcome to your campus learning portal!</p>

        <div className="dashboard-cards">
          <div className="dashboard-card">
            <h3>Browse Courses</h3>
            <p>Discover available courses.</p>
            <button type="button">View Courses</button>
          </div>

          <div className="dashboard-card">
            <h3>My Enrollments</h3>
            <p>Check your enrollment requests.</p>
            <button type="button">View Enrollments</button>
          </div>

          <div className="dashboard-card">
            <h3>Learning Resources</h3>
            <p>Access your course materials.</p>
            <button type="button">View Resources</button>
          </div>

          <div className="dashboard-card">
            <h3>Announcements</h3>
            <p>Read the latest campus announcements.</p>
            <button type="button">View Announcements</button>
          </div>
        </div>
      </main>
    </div>
  );
}

export default StudentDashboard;