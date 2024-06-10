// assets/js/controllers/actions_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["modal", "editLink", "deleteLink"];

  connect() {
    console.log("Connected");
  }

  showModal(event) {
    // this.modalTarget.classList.remove("translate-x-full");
    this.modalTarget.classList.remove("hidden");
    this.modalTarget.classList.remove("opacity-0");
  }

  closeModal() {
    // this.modalTarget.classList.add("translate-x-full");
    this.modalTarget.classList.add("hidden");
    this.modalTarget.classList.add("opacity-0");
  }
}
