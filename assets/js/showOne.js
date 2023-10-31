const btn = document.querySelector(".delete")

console.log("test");

btn.addEventListener("click", (e) => {
    // e.stopPropagation()
    const slug = e.target.dataset.slug;
    const confirmation = confirm(`Voulez vous vraiment supprimer le trick ${slug}`)
    const donnees = {
        cle1: 'valeur1',
        cle2: 'valeur2',
    };
    if (confirmation) {
        fetch(`/tricks/supprimer/${slug}`, {
            method: "POST",
        })
            .then((res) => {
                return res.json()
            }).then((data) => {
            location.href = "/"

        }).catch(err => {
            createToast("ce trick n'existe pas", "error")
        })
    }
})

function createToast(message, type) {
    let div = document.createElement("div")

    div.style.top = `${scrollY + 7}px`
    div.classList.add("absolute", "left-2")
    div.innerHTML = `
        <div id="message" class="alert alert-${type} w-max cursor-pointer">
            <span>${message}</ span>
        </div>`

    return div
}

function deleteToast(toast) {
    toast.remove();
}
