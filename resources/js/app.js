import './bootstrap';

import Alpine from 'alpinejs';
import 'toastr/build/toastr.min.css';
import toastr from 'toastr';
window.toastr = toastr;

window.Alpine = Alpine;

Alpine.start();
