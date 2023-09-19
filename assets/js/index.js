const allDeleteBtn = document.querySelectorAll(".delete")
console.log(allDeleteBtn);

allDeleteBtn.forEach((btn) => {
    btn.addEventListener("click", (e) => {
        e.stopPropagation()
        const slug = e.target.dataset.slug;
        const confirmation = confirm(`Voulez vous vraiment supprimer le trick ${slug}`)

        if (confirmation) {
        fetch(`/tricks/supprimer/${slug}`)
            .then((res) => {
                return res.json()
            }).then((data) => {
            const card = e.target.closest('.card');
            card.remove()
            const msg = data.message;
            const body = document.querySelector("body")
            const tricks = document.querySelector("#tricks")

            let div = createToast(msg, "info")
            div.addEventListener("click", () => {
                deleteToast(div)
            })
            tricks.appendChild(div)

            setTimeout(() => {
                // deleteToast(message)
            },5000)
        }).catch(err => {
            createToast("ce trick n'existe pas", "error")
        })
        }
    })
})

function createToast(message, type) {
    let div = document.createElement("div")

    div.style.top = `${scrollY+7}px`
    div.classList.add("absolute", "left-2")
    div.innerHTML = `
        <div id="message" class="alert alert-${type} w-max cursor-pointer">
<!--            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>-->
            <span>${message}</ span>
        </div>`

    return div
}

function deleteToast(toast){
    toast.remove();
}
