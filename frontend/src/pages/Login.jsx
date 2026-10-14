
import './Login.css';

function Login() {
  return (
    <div className="login-container">
      <h1>CampusConnect</h1>
      <p>Welcome back! Please log in to your account.</p>

      <form>
        <div className="form-group">
          <label htmlFor="email">Email</label>
          <input
            type="email"
            id="email"
            placeholder="Enter your email"
          />
        </div>

        <div className="form-group">
          <label htmlFor="password">Password</label>
          <input
            type="password"
            id="password"
            placeholder="Enter your password"
          />
        </div>

        <button type="submit">Login</button>
      </form>

      <p>
  Don't have an account? <a href="/register">Register as a student</a>
</p>
    </div>
  );
}

export default Login;