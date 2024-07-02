import { Controller } from "@hotwired/stimulus";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

export default class extends Controller {
  static targets = ["startDate", "endDate"];

  connect() {
    flatpickr(this.startDateTarget, {
      dateFormat: "Y-m-d",
      defaultDate: this.startDateTarget.value || null,
    });

    flatpickr(this.endDateTarget, {
      dateFormat: "Y-m-d",
      defaultDate: this.endDateTarget.value || null,
    });
  }
}
