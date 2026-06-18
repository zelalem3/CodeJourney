import { useState } from "react";

const Register = () => {
  const [username, setUsername] = useState("");
  const [bio, setbio] = useState("");
  const [password, setPassword] = useState("");

  function handleFormSubmit(e) {
    e.preventDefault();
    // Example:
    console.log("Login:", { username, password });
  }

  return (
    <form onSubmit={handleFormSubmit}>
      <div>
        <input
          type="text"
          value={username}
          onChange={(e) => setUsername(e.target.value)}
          placeholder="Set username"
        />
      </div>

      <div>
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          placeholder="Set password"
        />
      </div>

      <div>
        <input 
        type="password"
        value={password}
        onChange = {(e) => setbio(e.target.value)}
        placeholder="Set Bio"
        />
      </div>

      <button type="submit">Register</button>
    </form>
  );
};

export default Register;