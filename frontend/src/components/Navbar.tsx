import { useAuthStore } from '../store/authStore';

const Navbar = () => {
  const { user, token, logout } = useAuthStore();
  
  
  const isLoggedIn = !!token; 

  return (
    <nav>
      <h1>CodeJourney</h1>
      {isLoggedIn ? (
        <div>
          <span>Welcome, {user?.name}</span>
          <button onClick={logout}>Logout</button>
        </div>
      ) : (
        <div>
          <a href="/login">Login</a>
          <a href="/register">Register</a>
        </div>
      )}
    </nav>
  );
};

export default Navbar;