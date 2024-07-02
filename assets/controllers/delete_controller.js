// assets/controllers/delete_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["modal", "form", "token"];

    connect() {
        console.log("Delete Controller is connected!");
    }

    openModal(event) {
        event.preventDefault();
        const action = event.currentTarget.action;
        const token = event.currentTarget.querySelector("input[name='_token']").value;

        this.formTarget.setAttribute("action", action);
        this.tokenTarget.value = token;

        this.modalTarget.classList.remove("hidden");
        this.modalTarget.classList.add("flex");
    }

    cancel() {
        this.modalTarget.classList.add("hidden");
        this.modalTarget.classList.remove("flex");
    }
}
