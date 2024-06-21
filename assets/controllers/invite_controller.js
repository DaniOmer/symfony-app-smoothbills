// assets/controllers/invite_controller.js
import { Controller } from "@hotwired/stimulus";
import axios from "axios";

export default class extends Controller {
  static targets = ["modal", "form", "message"];

  connect() {
    console.log("Invite Modal is connected !");
  }

  openModal() {
    this.modalTarget.classList.remove("hidden");
  }

  closeModal() {
    this.modalTarget.classList.add("hidden");
  }

  async submitForm(event) {
    event.preventDefault();

    const formData = new FormData(this.formTarget);
    try {
      const response = await axios.post(this.formTarget.action, formData);
      //   this.messageTarget.textContent = "Invitation envoyée avec succès!";
      //   this.messageTarget.classList.remove("hidden");
      //   this.messageTarget.classList.add("text-green-600");
      this.closeModal();
    } catch (error) {
      this.messageTarget.textContent =
        "Cette adresse mail n'est pas disponible.";
      this.messageTarget.classList.remove("hidden");
      this.messageTarget.classList.add("text-red-600");
    }
  }
}
