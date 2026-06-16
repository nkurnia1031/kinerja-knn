@extends('Layout.out2')
@section('js')
    <script>
        const {
            createApp
        } = Vue

        app = createApp({
            data() {
                return {
                    user: '',
                    email: '',
                    pass: '',
                    token: '',
                    reset: false,
                    proses: false
                }
            },
            mounted() {
                // this.renderCF();

            },
            methods: {
                renderCF() {
                    app.token = '';
                    turnstile.ready(function() {
                        turnstile.render('#example-container', {
                            sitekey: '{{ $_SERVER['CF_SITE'] }}',
                            theme: 'light',
                            callback: function(token2) {
                                app.token = token2;
                                $('[data-toggle="tooltip"]').tooltip('hide')
                            },
                        });
                    });

                },
                onSubmit(event) {
                    this.proses = true;

                    axiosInstance.post('/ProsesLogin', {
                            username: this.user,
                            password: this.pass,
                            token: this.token
                        })
                        .then(function(response) {
                            app.proses = false;

                            data = response.data;
                            if (data.status) {
                                new Toast({
                                    message: data.data.msg,
                                    type: 'success'
                                });
                                localStorage.setItem('jwt', data.data.jwt);
                                setTimeout(function() {
                                    location.href = 'Dashboard';
                                }, 2000);

                            } else {
                                new Toast({
                                    message: data.data.msg,
                                    type: 'danger'
                                });
                            }
                            app.token = '';
                            // turnstile.reset('#example-container');

                        })
                        .catch(function(error) {
                            app.proses = false;
                            app.token = '';

                            // turnstile.reset('#example-container');
                            new Toast({
                                message: error,
                                type: 'danger'
                            });
                            console.log(error);
                        });

                },
                toggleReset(event) {
                    this.reset = !this.reset;
                    setTimeout(() => {
                        if (!this.reset) {
                            app.renderCF();
                        }

                        jalanScan();
                    }, 200);
                }

            }
        }).mount('#app')
        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
@section('isi')
    <style>
        input:-internal-autofill-selected {
            appearance: menulist-button;
            background-image: none !important;
            background-color: #ffffff2e !important;
            color: fieldtext !important;
        }

        .login-bg {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            min-height: 100vh;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
    <!-- Outer Row -->
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh">
            <div class="col-lg-5 col-12 ">
                <div class="card o-hidden border-0" style="background-color: #fff0">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card login-card border-0 p-2 p-lg-5" id="app">
                                    <div class="text-center">
                                        <div class="mb-4">
                                            <i class="fas fa-chart-line fa-4x text-white"></i>
                                        </div>
                                        <h1 class="h4 text-white mb-1">Sistem Penilaian Kinerja</h1>
                                        <h1 class="h6 text-white-50 mb-4">
                                            <Transition name="custom-classes2"
                                                enter-active-class="animate__animated animate__rubberBand ">
                                                <small v-if="!reset">Masuk dengan Username dan Password anda</small>
                                            </Transition>
                                        </h1>
                                    </div>
                                    <Transition name="custom-classes"
                                        enter-active-class="animate__animated animate__rubberBand ">
                                        <form v-if="!reset" class="user" @submit.prevent="onSubmit" method="POST">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-white rounded-circle p-3"
                                                        style="margin-right: -10%;z-index: 2;">
                                                        <i style="width: 20px;height: 20px;"
                                                            class="text-center fas fa-user text-primary"></i>
                                                    </div>
                                                    <input type="text"
                                                        class="form-control form-control-lg border-0 rounded-pill"
                                                        style="padding-left: 12%;color:white;background-color: #ffffff2e !important"
                                                        id="exampleInputEmail" v-model="user" placeholder="Username"
                                                        aria-describedby="emailHelp" name="user" autocomplete="off"
                                                        required="">
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <div class="d-flex align-items-center ">
                                                    <input type="password"
                                                        class="form-control form-control-lg border-0 rounded-pill"
                                                        style="padding-right: 12%;color:white;background-color: #ffffff2e !important"
                                                        id="exampleInputpass" v-model="pass" placeholder="********"
                                                        aria-describedby="emailHelp" name="pass" autocomplete="off"
                                                        required="">
                                                    <div class="bg-white rounded-circle p-3"
                                                        style="margin-left: -10%;z-index: 2;">
                                                        <i style="width: 20px;height: 20px;"
                                                            class="text-center fas fa-lock text-primary"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-center">
                                                <div id="example-container"></div>
                                            </div>

                                            <div class="d-flex flex-row-reverse">
                                                <button disabled="disabled" v-if="proses"
                                                    class="btn btn-block rounded-pill btn-secondary disabled">
                                                    <i class="fas fa-spinner fa-spin"></i> Memproses...
                                                </button>
                                                <button v-else type="submit" name="login" value="1"
                                                    class="btn bg-white text-primary btn-block rounded-pill font-weight-bold">
                                                    <i class="fas fa-sign-in-alt"></i> Login
                                                </button>
                                            </div>
                                            
                                            <div class="text-center mt-4">
                                                <small class="text-white-50">
                                                    <i class="fas fa-shield-alt"></i> 
                                                    Sistem Penilaian Kinerja Karyawan v1.0
                                                </small>
                                            </div>
                                        </form>
                                    </Transition>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
