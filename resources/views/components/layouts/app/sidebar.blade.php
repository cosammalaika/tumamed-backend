<!doctype html>
<html lang="en">


<head>
    @include('includes.adminHeader')
</head>

<body data-layout="horizontal">


    <div id="layout-wrapper">


        @include('includes.topbar')
        @include('includes.topnav')


        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </div>

            @include('includes.Footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('admin/minia/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <!-- pace js -->
    <script src="{{ asset('admin/minia/assets/libs/pace-js/pace.min.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('admin/minia/assets/libs/apexcharts/apexcharts.min.js') }}"></script>


    <!-- Required datatable js -->
    <script src="{{ asset('admin/minia/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script src="{{ asset('admin/minia/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>


    <!-- Plugins js-->
    <script src="{{ asset('admin/minia/assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}">
    </script>
    <!-- page init -->
    <script src="{{ asset('admin/minia/assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('admin/minia/js/datatables-init.js') }}"></script>

    <script>
        // Apply persisted theme before main app script
        (function () {
            try {
                var mode = localStorage.getItem('fixitzed-layout-mode');
                if (mode === 'dark' || mode === 'light') {
                    document.body.setAttribute('data-layout-mode', mode);
                    document.body.setAttribute('data-topbar', mode);
                    document.body.setAttribute('data-sidebar', mode);
                }
            } catch (e) {}
        })();
    </script>

    <script src="{{ asset('admin/minia/assets/js/app.js') }}"></script>

    <script>
        (function () {
            const flash = {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
            };

            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Notice',
            };

            function showAlert(type, message, options = {}) {
                if (!message) return Promise.resolve();
                const base = {
                    icon: type,
                    title: titles[type] ?? 'Notice',
                    text: message,
                    confirmButtonColor: '#F1592A',
                    confirmButtonText: 'OK',
                };

                if (options.toast) {
                    return Swal.fire({
                        toast: true,
                        position: options.position || 'top-end',
                        icon: type,
                        title: message,
                        timer: options.timer || 4000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });
                }

                return Swal.fire({ ...base, ...options });
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (flash.success) showAlert('success', flash.success);
                if (flash.error) showAlert('error', flash.error);
                if (flash.warning) showAlert('warning', flash.warning);
            });

            window.addEventListener('flash-message', function (event) {
                const rawDetail = event.detail ?? {};
                const detail = Array.isArray(rawDetail) ? (rawDetail[0] ?? {}) : rawDetail;
                const type = detail.type || 'info';
                const message = detail.message;
                const redirect = detail.redirect;
                const swalOptions = {};
                if (detail.timer) {
                    swalOptions.timer = detail.timer;
                    swalOptions.timerProgressBar = true;
                    swalOptions.showConfirmButton = false;
                }
                if (detail.toast) {
                    swalOptions.toast = true;
                    if (detail.position) swalOptions.position = detail.position;
                }

                showAlert(type, message, swalOptions).then(() => {
                    if (redirect) {
                        window.location.assign(redirect);
                    }
                });
            });
        })();
    </script>

    <script>
        // Persist theme on toggle and re-apply after Livewire navigation
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#mode-setting-btn')) return;
            setTimeout(function () {
                var mode = document.body.getAttribute('data-layout-mode') === 'dark' ? 'dark' : 'light';
                try { localStorage.setItem('fixitzed-layout-mode', mode); } catch (e) {}
            }, 0);
        });
        window.addEventListener('livewire:navigated', function () {
            var mode = localStorage.getItem('fixitzed-layout-mode');
            if (mode === 'dark' || mode === 'light') {
                document.body.setAttribute('data-layout-mode', mode);
                document.body.setAttribute('data-topbar', mode);
                document.body.setAttribute('data-sidebar', mode);
            }
        });
    </script>

    <script>
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-confirm-event]');
            if (!trigger) {
                return;
            }

            event.preventDefault();

            const eventName = trigger.getAttribute('data-confirm-event');
            if (!eventName) {
                return;
            }

            const title = trigger.getAttribute('data-confirm-title') || 'Are you sure?';
            const message = trigger.getAttribute('data-confirm-message') || 'This action cannot be undone.';
            const icon = trigger.getAttribute('data-confirm-icon') || 'warning';

            let payload = {};
            const rawPayload = trigger.getAttribute('data-confirm-payload');
            if (rawPayload) {
                try {
                    payload = JSON.parse(rawPayload);
                } catch (e) {
                    console.warn('Unable to parse data-confirm-payload', e);
                }
            }

            const id = trigger.getAttribute('data-confirm-id');
            if (id !== null) {
                payload.id = payload.id ?? (isNaN(id) ? id : Number(id));
            }

            Swal.fire({
                title,
                text: message,
                icon,
                showCancelButton: true,
                confirmButtonColor: '#F1592A',
                cancelButtonColor: '#6c757d',
                confirmButtonText: trigger.getAttribute('data-confirm-button') || 'Yes, proceed',
                cancelButtonText: trigger.getAttribute('data-cancel-button') || 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    Livewire.dispatch(eventName, payload);
                }
            });
        });
    </script>

    <script>
        (function () {
            const permissions = new Set(@json(auth()->user()?->getAllPermissions()->pluck('name') ?? []));

            function applyPermissionGuards(root) {
                if (!root) return;
                root.querySelectorAll('[data-permission]').forEach((el) => {
                    const required = (el.dataset.permission || '')
                        .split('|')
                        .map((value) => value.trim())
                        .filter(Boolean);

                    if (required.length && !required.every((permission) => permissions.has(permission))) {
                        el.remove();
                    }
                });

                root.querySelectorAll('[data-permission-any]').forEach((el) => {
                    const options = (el.dataset.permissionAny || '')
                        .split('|')
                        .map((value) => value.trim())
                        .filter(Boolean);

                    if (options.length && !options.some((permission) => permissions.has(permission))) {
                        el.remove();
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                applyPermissionGuards(document);
            });

            window.addEventListener('livewire:navigated', function () {
                applyPermissionGuards(document);
            });
        })();
    </script>
    <!-- color picker js -->
    <script src="{{ asset('admin/minia/assets/libs/@simonwep/pickr/pickr.min.js') }}"></script>
    <script src="{{ asset('admin/minia/assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>

    <!-- datepicker js -->
    <script src="{{ asset('admin/minia/assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    @stack('scripts')

</body>
</html>
