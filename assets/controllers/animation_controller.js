import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["listItems", "images"];

  initialize() {
    this.currentIndex = 0;
  }

  connect() {
    this.setupListeners();
    this.showImage(0);
  }

  setupListeners() {
    this.listItemsTargets.forEach((item, index) => {
      item.addEventListener("mouseover", () => this.handleMouseOver(index));
      item.addEventListener("mouseleave", () => this.handleMouseLeave(index));
    });
  }

  handleMouseOver(index) {
    this.listItemsTargets.forEach((item, idx) => {
      item.classList.toggle("custom-list", idx === index);
    });
    this.showImage(index);
  }

  //   handleMouseLeave(index) {
  //     this.listItemsTargets[index].classList.remove("custom-list");
  //   }

  showImage(index) {
    this.imagesTargets.forEach((image, idx) => {
      image.classList.toggle("hidden", idx !== index);
    });
  }
}
