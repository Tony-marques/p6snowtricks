const allDeleteBtn = document.querySelectorAll(".delete")

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
                deleteToast(message)
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
            <span>${message}</ span>
        </div>`

    return div
}

function deleteToast(toast){
    toast.remove();
}
