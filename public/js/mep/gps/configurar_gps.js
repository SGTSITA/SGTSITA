const btnActivar = document.querySelectorAll(".btn-config-gps")

btnActivar.forEach((btn) =>{
    
    btn.addEventListener('click',()=>{
        alert(btn.dataset.gps)    
    })
})

function configuracionGps(){
    
}