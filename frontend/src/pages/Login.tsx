import { useState } from "react";
import api from '../api/axios';


const Login = () => {
  const [email, setemail] = useState("");
  const [password, setPassword] = useState("");

  async function handleFormSubmit(e) {


    e.preventDefault();
    try{
    const response = await api.post("/auth/login",{
      email,
      password

    })
    console.log("login successful", response.data)
  }
  catch(error)
  {
    console.log(error);
  }
    // Example:
    console.log("Login:", { email, password });
  }

  return (
    <form onSubmit={handleFormSubmit}>
      <div>
        <input
          type="text"
          value={email}
          onChange={(e) => setemail(e.target.value)}
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

      <button type="submit">Login</button>
    </form>
  );
};

export default Login;