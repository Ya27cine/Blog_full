

function onClickBtnLike(e){ 
    e.preventDefault();
    const spanCournt = this.querySelector('span.link-like');

    axios.get(this.href).then( 
        function(reponse){

        const icon =    document.querySelector("#icon-like"+reponse.data.post);

        console.log(icon.classList)

        spanCournt.textContent = reponse.data.likes;

        if(icon.classList.contains('fas')){
            icon.classList.replace('fas', 'far')
        }else{
            icon.classList.replace('far', 'fas')
        }
    })
}

document.querySelectorAll('a.link-like')
          .forEach( function(link) {
            link.addEventListener("click", onClickBtnLike);
});
