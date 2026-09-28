import './bootstrap';
import Swal from 'sweetalert2';
import { createIcons, icons } from 'lucide';

import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;
window.lucide = { createIcons, icons };

// Karena Vite menggunakan <script type="module"> (deferred), HTML sudah di-load saat ini berjalan
createIcons();
