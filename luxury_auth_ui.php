<?php
// ---------- DATABASE CONNECTION ----------
$serverName = "AL_SHEEFRA\\SQLEXPRESS";
$database   = "alsheefradb";
$uid        = "Raheel";
$pass       = "Ltimcloud@2026";

$connectionInfo = ["Database"=>$database, "UID"=>$uid, "PWD"=>$pass];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if(!$conn){ die("DB Connection Failed"); }

$popup="";

// ---------- SIGNUP ----------
if(isset($_POST["action"]) && $_POST["action"]==="signup"){
  $name=$_POST["su_name"]; $phone=$_POST["su_phone"]; $email=$_POST["su_email"]; $p1=$_POST["su_password"]; $p2=$_POST["su_password2"];
  if($p1!==$p2){
    $popup="Passwords do not match!";
  } else {
    $query="INSERT INTO ASAppUsers (ASUserName,ASPhoneNumber,ASEmailID,ASPassword,ASReEnterPassword) VALUES (?,?,?,?,?)";
    $stmt=sqlsrv_query($conn,$query,[$name,$phone,$email,$p1,$p2]);
    $popup=$stmt?"Signup successful! Please login.":"Error inserting data.";
  }
}

// ---------- LOGIN ----------
if(isset($_POST["action"]) && $_POST["action"]==="login"){
  $email=$_POST["li_email"]; $pass=$_POST["li_password"];
  $query="SELECT * FROM ASAppUsers WHERE LOWER(ASEmailID)=LOWER(?) AND ASPassword=?";
  $stmt=sqlsrv_query($conn,$query,[$email,$pass]);
  $row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);

  if($row){
      header("Location: dashboard.php");
      exit;
  } else {
      $popup="Invalid Email or Password!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Al Sheefra — Login / Signup</title>

<style>
:root{
  --blue:#0066ff;
  --blue-dark:#0045b8;
  --white:#ffffff;
  --shadow:rgba(0,102,255,0.25);
  --radius:20px;
  font-family:"Poppins",sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}

/* -------- Background -------- */
body{
  height:100vh;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#e8f1ff,#f8fbff);
  padding:20px;color:#003266;
}

/* -------- Main Card -------- */
.card{
  width:100%;max-width:440px;
  background:#ffffffaa;
  backdrop-filter:blur(14px);
  border:1px solid #cfe2ff;
  padding:32px;border-radius:var(--radius);
  box-shadow:0 8px 25px rgba(0,0,0,0.10);
}

/* Titles */
.title{font-size:28px;font-weight:700;margin-bottom:6px;text-align:center;color:#003680;}
.subtitle{text-align:center;margin-bottom:22px;color:#4c5b73;}

/* Toggle */
.toggle{
  display:flex;background:#e6f0ff;padding:5px;border-radius:16px;margin-bottom:20px;
}
.toggle button{
  flex:1;border:0;background:transparent;color:#4c5b73;
  padding:10px;border-radius:12px;font-weight:600;cursor:pointer;
  transition:0.3s;
}
.toggle button.active{
  background:#0066ff;color:white;
  box-shadow:0 4px 12px rgba(0,102,255,0.35);
}

/* Fields */
.field{margin-bottom:16px;display:flex;flex-direction:column;gap:6px;}
.field label{font-size:14px;color:#003266;}
.field input{
  padding:12px 14px;border-radius:12px;
  border:1px solid #cfe2ff;background:#f4f8ff;color:#003266;
}

/* Buttons */
.button{
  width:100%;padding:12px;border:0;border-radius:14px;
  background:linear-gradient(135deg,#007bff,#0059d4);
  color:#fff;font-size:16px;margin-top:8px;cursor:pointer;font-weight:600;
  transition:0.25s;box-shadow:0 6px 18px var(--shadow);
}
.button:hover{transform:translateY(-2px);}

/* Links */
.muted{text-align:center;margin-top:12px;font-size:14px;color:#4c5b73;}
.muted a{color:#0066ff;text-decoration:none;font-weight:600;}
.hidden{display:none;}

/* -------- MODERN BLUE-WHITE DIALOG -------- */
.dialog-overlay{
  position:fixed;inset:0;background:rgba(0,60,150,0.20);
  backdrop-filter:blur(8px);
  display:none;justify-content:center;align-items:center;
  z-index:9999;
}
.dialog-card{
  width:330px;background:#ffffffee;padding:25px;border-radius:22px;
  text-align:center;border:1px solid rgba(0,102,255,0.15);
  box-shadow:0 10px 35px var(--shadow);
  animation:popUp 0.25s ease-out;
}
.dialog-card h3{
  margin-bottom:10px;font-size:22px;color:#00388b;font-weight:700;
}
.dialog-card p{
  font-size:15px;color:#4d4d4d;
}
.dialog-btn{
  margin-top:20px;padding:12px 30px;border:0;
  border-radius:12px;background:linear-gradient(135deg,#007bff,#0059d4);
  color:white;font-size:15px;cursor:pointer;
  box-shadow:0 4px 12px rgba(0,102,255,0.35);
  transition:0.2s;
}
.dialog-btn:hover{transform:translateY(-3px);}
@keyframes popUp{
  from{transform:scale(0.8);opacity:0;}
  to{transform:scale(1);opacity:1;}
}
</style>
</head>

<body>

<!-- MODERN WHITE-BLUE DIALOG -->
<div id="dialogBox" class="dialog-overlay" style="<?php echo $popup ? 'display:flex;' : ''; ?>">
  <div class="dialog-card">
    <h3>Message</h3>
    <p><?php echo $popup; ?></p>
    <button class="dialog-btn" onclick="closeDialog()">OK</button>
  </div>
</div>

<main class="card">
  <div class="toggle">
    <button id="tabLogin" class="active">Login</button>
    <button id="tabSignup">Sign Up</button>
  </div>

  <h1 id="authTitle" class="title">Welcome Back</h1>
  <p class="subtitle">Good to see you ! </p>

  <form method="post" id="loginPanel">
    <input type="hidden" name="action" value="login">

    <div class="field">
      <label>Email</label>
      <input type="email" name="li_email" required>
    </div>

    <div class="field">
      <label>Password</label>
      <input type="password" name="li_password" required>
    </div>

    <button class="button">Login</button>
    <p class="muted">New user? <a href="#" id="toSignup">Create account</a></p>
  </form>

  <form method="post" id="signupPanel" class="hidden">
    <input type="hidden" name="action" value="signup">

    <div class="field">
      <label>Full Name</label>
      <input type="text" name="su_name" required>
    </div>

    <div class="field">
      <label>Phone Number</label>
      <input type="tel" name="su_phone" required>
    </div>

    <div class="field">
      <label>Email</label>
      <input type="email" name="su_email" required>
    </div>

    <div class="field">
      <label>Password</label>
      <input type="password" name="su_password" required>
    </div>

    <div class="field">
      <label>Confirm Password</label>
      <input type="password" name="su_password2" required>
    </div>

    <button class="button">Sign Up</button>
    <p class="muted">Already registered? <a href="#" id="toLogin">Login</a></p>
  </form>
</main>

<script>
// close dialog
function closeDialog(){
  document.getElementById("dialogBox").style.display="none";
}

// toggle switch
const tL=document.getElementById("tabLogin"),
      tS=document.getElementById("tabSignup"),
      lp=document.getElementById("loginPanel"),
      sp=document.getElementById("signupPanel"),
      title=document.getElementById("authTitle");

function showLogin(){
  tL.classList.add("active"); tS.classList.remove("active");
  lp.classList.remove("hidden"); sp.classList.add("hidden");
  title.textContent="Welcome Back";
}
function showSignup(){
  tS.classList.add("active"); tL.classList.remove("active");
  sp.classList.remove("hidden"); lp.classList.add("hidden");
  title.textContent="Create Account";
}
document.getElementById("toSignup").onclick=e=>{e.preventDefault();showSignup();}
document.getElementById("toLogin").onclick=e=>{e.preventDefault();showLogin();}
tL.onclick=()=>showLogin(); tS.onclick=()=>showSignup();
</script>

</body>
</html>
