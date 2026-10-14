
import { Link } from 'react-router-dom';
import './Login.css';

function Register() {
  return (
    <div className="login-container">
      <h1>CampusConnect</h1>
      <p>Create your student account</p>

      <form>
        <div className="form-group">
          <label htmlFor="name">Full Name</label>
          <input
            type="text"
            id="name"
            placeholder="e.g. Alex Morgan"
            autoComplete="name"
          />
        </div>

        <div className="form-group">
          <label htmlFor="email">Email Address</label>
          <input
            type="email"
            id="email"
            placeholder="e.g. student@campus.edu"
            autoComplete="email"
          />
        </div>

        <div className="form-group">
          <label htmlFor="password">Password</label>
          <input
            type="password"
            id="password"
            placeholder="Create a password (min. 6 characters)"
            autoComplete="new-password"
          />
        </div>

        <button type="submit">Register</button>
      </form>

      <p>
        Already have an account? <Link to="/">Login</Link>
      </p>
      <p>Registration is for students only.</p>
    </div>
  );
}

export default Register;