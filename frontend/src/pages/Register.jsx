
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
            placeholder="Enter your full name"
          />
        </div>

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
            placeholder="Create a password"
          />
        </div>

        <button type="submit">Register</button>
      </form>

      <p>
  Already have an account? <a href="/">Login</a>
</p>
<p>Registration is for students only.</p>
    </div>
  );
}

export default Register;