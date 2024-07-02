import { Controller } from "@hotwired/stimulus";
import axios from "axios";

export default class extends Controller {
    static targets = ['input', 'results', 'resultList'];

    connect() {
        console.log("Search controller connected!");
        this.hideResults();
    }

    async search() {
        const searchQuery = this.inputTarget.value.trim();
        if (searchQuery.length >= 3) {
            try {
                const response = await axios.get(`/search/${encodeURIComponent(searchQuery)}`);
                const results = response.data;
                this.displaySearchResults(results);
            } catch (error) {
                console.error("Error fetching search results:", error);
                this.hideResults();
            }
        } else {
            this.hideResults();
            console.log("Please enter at least 3 characters.");
        }
    }

    displaySearchResults(results) {
        if (!this.resultsTarget) return;

        this.clearResults();

        if (results.length > 0) {
            const fragment = document.createDocumentFragment();
            results.forEach(result => {
                const item = this.buildResultItem(result);
                fragment.appendChild(item);
            });
            this.resultListTarget.appendChild(fragment);
            this.showResults();
        } else {
            this.hideResults();
        }
    }

    buildResultItem(result) {
        const item = document.createElement('li');
        const link = document.createElement('a');
        link.href = result.link;
        link.textContent = result.name;
        link.classList.add('text-sm', 'text-black', 'px-4', 'py-2');
        link.setAttribute('data-action', 'click->search#redirectToLink');
        link.dataset.link = result.link;
        if (result.highlighted) {
            link.classList.add('font-bold', 'text-blue-500');
        }
        item.appendChild(link);
        return item;
    }

    redirectToLink(event) {
        event.preventDefault();
        const link = event.currentTarget.dataset.link;
        console.log("Redirecting to:", link);
        window.location.href = link;
    }

    showResults() {
        this.resultsTarget.classList.remove('hidden');
    }

    hideResults() {
        this.resultsTarget.classList.add('hidden');
    }

    clearResults() {
        this.resultListTarget.innerHTML = '';
    }

    get inputTarget() {
        return this.targets.find('input');
    }

    get resultsTarget() {
        return this.targets.find('results');
    }

    get resultListTarget() {
        return this.targets.find('resultList');
    }
   
}
