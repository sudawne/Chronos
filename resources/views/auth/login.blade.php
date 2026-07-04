<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Xác thực hệ thống | CHRONOS AI</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface": "#f8fafc",
                        "background": "#f3f6fd",
                        "primary": "#5949be",
                        "primary-dim": "#4338ca",
                        "on-surface": "#0f172a",
                        "on-surface-variant": "#475569",
                        "outline-variant": "#cbd5e1"
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Manrope', sans-serif; }
        .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24 }
        .signature-gradient { background: linear-gradient(135deg, #5949be 0%, #6C5DD3 50%, #818cf8 100%) }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(89, 73, 190, 0.15);
        }
        @keyframes float { 0%, 100% { transform: translateY(0) rotate(2deg); } 50% { transform: translateY(-10px) rotate(0deg); } }
        .animate-float { animation: float 6s ease-in-out infinite }
        .reveal { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0 }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .delay-1 { animation-delay: 0.1s }
        .delay-2 { animation-delay: 0.2s }
    </style>
</head>
<body class="bg-background text-on-surface overflow-x-hidden">
<main class="min-h-screen flex flex-col md:flex-row relative">

    <section class="relative w-full md:w-1/2 min-h-[300px] md:min-h-screen flex items-center justify-center p-8 overflow-hidden signature-gradient">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-[-15%] right-[-10%] w-[500px] h-[500px] rounded-full bg-indigo-200/10 blur-[120px]"></div>
        
        <div class="relative z-10 max-w-md text-center md:text-left space-y-6">
            <h1 class="font-headline text-4xl md:text-6xl font-extrabold text-white leading-tight tracking-tight reveal">
                CHRONOS
            </h1>
            <p class="text-base md:text-lg text-indigo-100 opacity-90 reveal delay-1">
                Hệ thống quản lý và điểm danh tự động theo thời gian thực.
            </p>
            <div class="pt-4 hidden md:flex justify-center md:justify-start reveal delay-2">
                <div class="relative w-64 h-64 rounded-3xl overflow-hidden shadow-2xl animate-float border-4 border-white/10">
                    <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=600&auto=format&fit=crop" alt="AI Technology"/>
                </div>
            </div>
        </div>
    </section>

    <section class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-12 bg-slate-50 relative">
        <div class="w-full max-w-md space-y-8 relative z-10 glass-card p-8 rounded-[2.5rem]">
            
            <div class="space-y-3 reveal">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl signature-gradient flex items-center justify-center shadow-md">
                        <span class="material-symbols-outlined text-white text-xl" style="font-variation-settings: 'FILL' 1;">memory</span>
                    </div>
                    <span class="font-headline text-xl font-black tracking-wider text-primary">CHRONOS</span>
                </div>

                @if($errors->has('login_error'))
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">error</span> {{ $errors->first('login_error') }}
                    </div>
                @endif
                @if($errors->any() && !$errors->has('login_error'))
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div id="login-section" class="space-y-6 reveal delay-1">
                <div>
                    <h2 class="font-headline text-2xl font-bold text-on-surface">Chào mừng bạn</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Vui lòng nhập thông tin để truy cập hệ thống</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-on-surface-variant px-1">Địa chỉ Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-xl absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">mail</span>
                            <input class="block w-full pl-11 pr-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="email" value="{{ old('email') }}" placeholder="nguyen@vnkgu.edu.vn" type="email" required/>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-on-surface-variant px-1">Mật khẩu</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-xl absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
                            <input class="block w-full pl-11 pr-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="password" placeholder="••••••••" type="password" required/>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs font-semibold">
                        <label class="flex items-center gap-2 cursor-pointer">
                        </label>
                    </div>

                    <button class="w-full py-3.5 mt-2 rounded-xl signature-gradient text-white font-headline font-bold text-sm hover:scale-[1.02] transition-all shadow-md shadow-primary/25" type="submit">
                        Đăng nhập
                    </button>
                </form>
            </div>

            <div id="register-section" class="space-y-6 hidden">
                <div>
                    <h2 class="font-headline text-2xl font-bold text-on-surface">Đăng ký tài khoản</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Khởi tạo tài khoản mới</p>
                </div>

                <form action="{{ route('register') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-on-surface-variant px-1">Họ và Tên</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-xl absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">person</span>
                            <input class="block w-full pl-11 pr-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Nguyễn Văn A" type="text" required/>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-on-surface-variant px-1">Địa chỉ Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-xl absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">mail</span>
                            <input class="block w-full pl-11 pr-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="email" value="{{ old('email') }}" placeholder="nguyen@vnkgu.edu.vn" type="email" required/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-on-surface-variant px-1">Mật khẩu</label>
                            <input class="block w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="password" placeholder="Tối thiểu 6 ký tự" type="password" required/>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-on-surface-variant px-1">Xác nhận</label>
                            <input class="block w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm focus:ring-4 focus:ring-primary/10 focus:border-primary/30 transition-all outline-none" name="password_confirmation" placeholder="Nhập lại mật khẩu" type="password" required/>
                        </div>
                    </div>

                    <button class="w-full py-3.5 mt-2 rounded-xl bg-slate-900 text-white font-headline font-bold text-sm hover:bg-slate-800 hover:scale-[1.02] transition-all shadow-md" type="submit">
                        Tạo tài khoản
                    </button>
                </form>
            </div>

            <div class="relative flex items-center py-1">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink mx-4 text-slate-400 font-label text-[10px] uppercase tracking-[0.15em] font-bold">Hoặc tiếp tục bằng</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <a href="{{ Route::has('google.login') ? route('google.login') : '#' }}" class="flex items-center justify-center py-2.5 border border-slate-200 bg-white/50 rounded-xl hover:bg-white hover:shadow-sm transition-all group">
                    <img alt="Google" class="w-5 h-5 group-hover:scale-110 transition-transform" src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png"/>
                </a>
                <a href="{{ Route::has('facebook.login') ? route('facebook.login') : '#' }}" class="flex items-center justify-center py-2.5 border border-slate-200 bg-white/50 rounded-xl hover:bg-white hover:shadow-sm transition-all group">
                    <img alt="Facebook" class="w-5 h-5 group-hover:scale-110 transition-transform" src="https://cdn-icons-png.flaticon.com/512/124/124010.png"/>
                </a>
            </div>

            <p id="toggle-footer-text" class="text-center text-sm font-medium text-on-surface-variant">
                Chưa có tài khoản? 
                <button onclick="switchForm('register')" class="text-primary font-bold hover:underline ml-1">Đăng ký ngay</button>
            </p>
        </div>
    </section>
</main>

<script>
    function switchForm(action) {
        const loginSection = document.getElementById('login-section');
        const registerSection = document.getElementById('register-section');
        const footerText = document.getElementById('toggle-footer-text');

        if (action === 'register') {
            loginSection.classList.add('hidden');
            registerSection.classList.remove('hidden');
            footerText.innerHTML = `Đã có tài khoản? <button type="button" onclick="switchForm('login')" class="text-primary font-bold hover:underline ml-1">Đăng nhập</button>`;
        } else {
            registerSection.classList.add('hidden');
            loginSection.classList.remove('hidden');
            footerText.innerHTML = `Chưa có tài khoản? <button type="button" onclick="switchForm('register')" class="text-primary font-bold hover:underline ml-1">Đăng ký ngay</button>`;
        }
    }

    // Tự động giữ form Đăng ký nếu có lỗi validate từ Register
    @if(old('name') || $errors->has('name') || $errors->has('password_confirmation'))
        switchForm('register');
    @endif
</script>
</body>
</html>