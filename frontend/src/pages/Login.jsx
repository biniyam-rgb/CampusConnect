
import { Link } from 'react-router-dom';
import './Login.css';

function Login() {
  return (
    <div className="login-container">
      <h1>CampusConnect</h1>
      <p>Welcome back! Please log in to your account.</p>

      <form>
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
            placeholder="Enter your password"
            autoComplete="current-password"
          />
        </div>

        <button type="submit">Login</button>
      </form>

      <p>
        Don't have an account? <Link to="/register">Register as a student</Link>
      </p>
    </div>
  );
}

export default Login;