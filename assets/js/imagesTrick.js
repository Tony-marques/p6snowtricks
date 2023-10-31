// document.querySelectorAll(".add_item_link").forEach((btn) => {
//    btn.addEventListener("click", addFormToCollection);
// console.log(btn);

// });


const btn = document.querySelector(".add_item_link")
btn.addEventListener("click", (e) => {
  console.log("click");
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


const addFormToCollection = (e) => {
  console.log("click");
  //  const collectionHolder = document.querySelector(
  //     "." + e.currentTarget.dataset.collectionHolderClass
  //  );

  //  const item = document.createElement("li");

  //  item.innerHTML = collectionHolder.dataset.prototype.replace(
  //     /__name__/g,
  //     collectionHolder.dataset.index
  //  );

  //  collectionHolder.appendChild(item);

  //  collectionHolder.dataset.index++;
};
