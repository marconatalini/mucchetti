import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["form"];
  connect() {
    // this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/employee_controller.js';
  }

  setEmailAuto() {
    let firstName = this.formTarget.querySelector('#employee_firstName').value.toLowerCase();
    let lastName = this.formTarget.querySelector('#employee_lastName').value.toLowerCase();
    let emailEl = this.formTarget.querySelector('#employee_email');
    if (firstName.length > 3 && lastName.length > 3 && emailEl.value < 3) {
      emailEl.value = firstName + "." + lastName + "@europrofiligroup.it";
    }
  }
}
