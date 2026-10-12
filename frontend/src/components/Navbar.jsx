
import { Link } from 'react-router-dom';
import './Navbar.css';

function Navbar() {
  return (
    <nav className="navbar">
      <Link to="/" className="navbar-brand">
        CampusConnect
      </Link>

      <div className="navbar-links">
        <Link to="/student-dashboard">Student</Link>
        <Link to="/lecturer-dashboard">Lecturer</Link>
        <Link to="/admin-dashboard">Admin</Link>
        <Link to="/register">Register</Link>
      </div>
    </nav>
  );
}

export default Navbar;