
import './AdminDashboard.css';

function AdminDashboard() {
  return (
    <div className="admin-dashboard">
      <header className="admin-header">
        <h1>CampusConnect</h1>
        <span>Admin Portal</span>
      </header>

      <main className="admin-main">
        <h2>Admin Dashboard</h2>
        <p>Welcome! Manage the CampusConnect system.</p>

        <div className="admin-cards">
          <div className="admin-card">
            <h3>User Management</h3>
            <p>Create lecturer accounts and manage users and roles.</p>
            <button type="button">Manage Users</button>
          </div>

          <div className="admin-card">
            <h3>Course Management</h3>
            <p>Create, update, and manage courses.</p>
            <button type="button">Manage Courses</button>
          </div>

          <div className="admin-card">
            <h3>Enrollment Requests</h3>
            <p>Review student requests and approve or reject them.</p>
            <button type="button">Review Enrollments</button>
          </div>

          <div className="admin-card">
            <h3>Announcements</h3>
            <p>Publish announcements for students and lecturers.</p>
            <button type="button">Manage Announcements</button>
          </div>
        </div>
      </main>
    </div>
  );
}

export default AdminDashboard;