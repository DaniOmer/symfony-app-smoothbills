import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["listItems", "images"];

  
    initialize() {
        this.currentIndex = 0;
        this.intervalId = null;
    }

    connect() {
        console.log('Carousel controller connected');
        this.initializeVisibility();
        this.startInterval();
        this.setupListeners();
    }

    initializeVisibility() {
        this.imagesTargets.forEach((image, idx) => {
            image.classList.toggle('content-image', idx === 0);
            image.classList.toggle('hidden', idx !== 0);
        });
    }

    updateVisibility(index) {
        this.imagesTargets.forEach((image, idx) => {
            const shouldBeVisible = idx === index;
            image.classList.toggle('content-image', shouldBeVisible);
            image.classList.toggle('hidden', !shouldBeVisible);
        });
    }

    changeActiveItem() {
        this.listItemsTargets[this.currentIndex].classList.remove('custom-li-class');
        this.currentIndex = (this.currentIndex + 1) % this.listItemsTargets.length;
        this.listItemsTargets[this.currentIndex].classList.add('custom-li-class');
        this.updateVisibility(this.currentIndex);
    }

    startInterval() {
        this.intervalId = setInterval(() => this.changeActiveItem(), 3000);
    }

    stopInterval() {
        clearInterval(this.intervalId);
    }

    setupListeners() {
        this.listItemsTargets.forEach((item, index) => {
            item.addEventListener('mouseover', () => this.handleMouseOver(index));
            item.addEventListener('mouseleave', () => this.handleMouseLeave());
        });
    }

    handleMouseOver(index) {
        this.stopInterval();
        this.listItemsTargets[this.currentIndex].classList.remove('custom-li-class');
        this.currentIndex = index;
        this.listItemsTargets[this.currentIndex].classList.add('custom-li-class');
        this.updateVisibility(this.currentIndex);
    }

    handleMouseLeave() {
        this.startInterval();
        this.listItemsTargets[this.currentIndex].classList.remove('custom-li-class');
        this.listItemsTargets[this.currentIndex].classList.add('custom-li-class');
        this.updateVisibility(this.currentIndex);
    }
}
