const btn = document.querySelector(".delete")

console.log("test");

btn.addEventListener("click", (e) => {
    const slug = e.target.dataset.slug;
    const confirmation = confirm(`Voulez vous vraiment supprimer le trick ${slug}`)

    if (confirmation) {
        fetch(`/tricks/supprimer/${slug}`, {
            method: "POST",
        })
            .then((res) => {
                return res.json()
            }).then((data) => {
            location.href = "/"

        }).catch(err => {
            
        })
    }
})

