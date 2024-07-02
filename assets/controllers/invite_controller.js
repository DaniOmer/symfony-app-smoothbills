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
      if (response.status === 200 && response.data.success) {
        this.showMessage(response.data.success, "text-green-600");
        setTimeout(() => {
          this.closeModal();
          window.location.reload();
        }, 3000);
      } else if (response.data.error) {
        this.showMessage(response.data.error, "text-red-600");
      }
    } catch (error) {
      if (error.response && error.response.data && error.response.data.error) {
        this.showMessage(error.response.data.error, "text-red-600");
      } else {
        this.showMessage(
          "Une erreur s'est produite. Veuillez réessayer.",
          "text-red-600"
        );
      }
    }
  }

  showMessage(message, className) {
    this.messageTarget.textContent = message;
    this.messageTarget.classList.remove("hidden");
    this.messageTarget.classList.add(className);
  }
}
