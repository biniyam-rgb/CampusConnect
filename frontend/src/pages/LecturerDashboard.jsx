
import './LecturerDashboard.css';

function LecturerDashboard() {
  return (
    <div className="lecturer-dashboard">
      <header className="lecturer-header">
        <h1>CampusConnect</h1>
        <span>Lecturer Portal</span>
      </header>

      <main className="lecturer-main">
        <h2>Lecturer Dashboard</h2>
        <p>Welcome! Manage your courses and learning materials.</p>

        <div className="lecturer-cards">
          <div className="lecturer-card">
            <h3>My Courses</h3>
            <p>View the courses assigned to you.</p>
            <button type="button">View Courses</button>
          </div>

          <div className="lecturer-card">
            <h3>Learning Resources</h3>
            <p>Upload materials for your courses.</p>
            <button type="button">Manage Resources</button>
          </div>

          <div className="lecturer-card">
            <h3>Enrollment Information</h3>
            <p>View relevant student enrollment information.</p>
            <button type="button">View Enrollments</button>
          </div>

          <div className="lecturer-card">
            <h3>Announcements</h3>
            <p>Read announcements published by the Admin.</p>
            <button type="button">View Announcements</button>
          </div>
        </div>
      </main>
    </div>
  );
}

export default LecturerDashboard;