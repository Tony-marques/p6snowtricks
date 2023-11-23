const allDeleteBtn = document.querySelectorAll(".delete");
const msgFlashes = document.querySelector(".flash-messages");
const msg = document.querySelector(".msg");

msgFlashes.style.display = "none";

allDeleteBtn.forEach((btn) => {
   btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const slug = e.target.dataset.slug;
      const confirmation = confirm(
         `Voulez vous vraiment supprimer le trick ${slug}`
      );

      if (confirmation) {
         fetch(`/tricks/supprimer/${slug}`)
            .then((res) => {
               return res.json();
            })
            .then((data) => {
               msgFlashes.style.display = "flex";
               msg.textContent = data.message;
               const parent = e.target.closest(".card");
               parent.remove();
               console.log(data);
            })
            .catch((err) => {});
      }
   });
});
