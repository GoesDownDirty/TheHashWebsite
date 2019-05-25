$(document).ready(function() {
  if(Cookies.get("darkmode")=="1") {
    $("body").addClass("darkmode");
  }
});
  
function toggleDarkMode() {
  if(Cookies.get("darkmode")=="1") {
    Cookies.remove("darkmode");
    $("body").removeClass("darkmode");
    alert("Dark mode disabled.");
  } else {
    Cookies.set("darkmode","1", { expires : 3650 });
    $("body").addClass("darkmode");
    alert("Congratulations!  Dark mode unlocked!");
  }
}
