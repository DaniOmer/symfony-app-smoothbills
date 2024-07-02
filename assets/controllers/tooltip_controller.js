import { Controller } from "@hotwired/stimulus";

/*
 * The following line makes this controller "lazy": it won't be downloaded until needed
 * See https://github.com/symfony/stimulus-bridge#lazy-controllers
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ["container"];

  connect() {
    this.containerTargets.forEach((container) => {
      container.addEventListener("mouseenter", this.showTooltip.bind(this));
      container.addEventListener("mouseleave", this.hideTooltip.bind(this));
    });
  }

  showTooltip(event) {
    const container = event.currentTarget;
    const link = container.querySelector("a");

    if (link && link.classList.contains("pointer-events-none")) {
      const tooltip = document.createElement("div");
      tooltip.className =
        "tooltip absolute -top-1 left-full -ml-36 w-40 border border-secondary bg-secondary text-white text-sm text-center py-1 rounded-md z-20";
      tooltip.textContent = "Complétez votre profil pour continuer.";
      container.appendChild(tooltip);

      this.tooltip = tooltip;
    }
  }

  hideTooltip() {
    if (this.tooltip) {
      this.tooltip.remove();
      this.tooltip = null;
    }
  }
}
