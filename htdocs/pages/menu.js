const menuBtn = document.getElementById("menuTogg");

const sidebar = document.querySelector(".sidebar");

menuBtn.addEventListener("click", function(){
    if(sidebar.style.display === "block"){
        sidebar.style.display = "none";
    }
    else{
        sidebar.style.display = "block";
    }
});

document.addEventListener("click", function(e){
    if(!menuBtn.contains(e.target) && !sidebar.contains(e.target)){
        sidebar.style.display = "none";
    }
});