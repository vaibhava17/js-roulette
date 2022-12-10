window.addEventListener("load", function(){
    setTimeout(
        function open(event){
            document.querySelector(".popup").style.display = "block";
        },
        1000
    )
});


document.querySelector("#close").addEventListener("click", function(){
    document.querySelector(".popup").style.display = "none";
});
// document.querySelector(".close").addEventListener("click" , function(){
//     document.querySelector(".container").style.display = "none";
// });

// window.addEventListener("load" , function(){
//     setTimeout(
//         function open(event){
//             document.querySelector(".container").style.display = "block";
//         },
//         2000
//     )
// });