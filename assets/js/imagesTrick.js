const buttons = document.querySelectorAll(".add_item_link");
buttons.forEach((btn) => {
   btn.addEventListener("click", (e) => {
      const collectionHolder = document.querySelector(
         "." + e.currentTarget.dataset.collectionHolderClass
      );

      const item = document.createElement("li");

      item.innerHTML = collectionHolder.dataset.prototype.replace(
         /__name__/g,
         collectionHolder.dataset.index
      );

      collectionHolder.appendChild(item);

      collectionHolder.dataset.index++;
   });
});
