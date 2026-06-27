<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Login | Adventure</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Manrope:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "surface": "#f9f5ff",
                    "tertiary": "#93387f",
                    "background": "#f9f5ff",
                    "tertiary-fixed": "#fc92e0",
                    "surface-tint": "#2d49dc",
                    "on-background": "#2c2a51",
                    "surface-container-high": "#e3dfff",
                    "outline-variant": "#aca8d7",
                    "inverse-surface": "#0b082f",
                    "primary-fixed-dim": "#768aff",
                    "on-tertiary-fixed": "#3e0035",
                    "primary-dim": "#1b3bd0",
                    "inverse-primary": "#7287ff",
                    "surface-container-low": "#f3eeff",
                    "secondary": "#006573",
                    "surface-variant": "#ddd9ff",
                    "on-surface": "#2c2a51",
                    "secondary-fixed": "#54e3fc",
                    "on-secondary-fixed": "#003a43",
                    "surface-container-highest": "#ddd9ff",
                    "on-secondary": "#daf8ff",
                    "surface-bright": "#f9f5ff",
                    "on-primary": "#f3f1ff",
                    "primary": "#2d49dc",
                    "on-tertiary-container": "#630955",
                    "on-surface-variant": "#5a5781",
                    "on-tertiary": "#ffeef6",
                    "on-error-container": "#510017",
                    "primary-fixed": "#8899ff",
                    "on-tertiary-fixed-variant": "#6e165f",
                    "on-primary-fixed-variant": "#001b86",
                    "surface-container": "#e9e5ff",
                    "inverse-on-surface": "#9c98c6",
                    "error": "#b41340",
                    "on-secondary-container": "#004f5a",
                    "on-primary-container": "#00156e",
                    "on-primary-fixed": "#000000",
                    "error-container": "#f74b6d",
                    "on-secondary-fixed-variant": "#005965",
                    "tertiary-fixed-dim": "#ed85d1",
                    "secondary-fixed-dim": "#40d5ee",
                    "tertiary-dim": "#842b73",
                    "surface-container-lowest": "#ffffff",
                    "tertiary-container": "#fc92e0",
                    "error-dim": "#a70138",
                    "outline": "#75729e",
                    "secondary-dim": "#005864",
                    "on-error": "#ffefef",
                    "primary-container": "#8899ff",
                    "surface-dim": "#d4cfff",
                    "secondary-container": "#54e3fc"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "fontFamily": {
                    "headline": ["Plus Jakarta Sans"],
                    "body": ["Manrope"],
                    "label": ["Manrope"]
            }
          }
        }
      }
    </script>
<style>.material-symbols-outlined {
    font-variation-settings: "FILL" 0, "wght" 300, "GRAD" 0, "opsz" 24
    }
.signature-gradient {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #6366f1 100%)
    }
.glass-card {
    background: rgba(255, 255, 255, 0.72);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.45);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 25px 50px -12px rgba(44, 42, 81, 0.12), inset 0 0 15px 2px rgba(255, 255, 255, 0.5)
    }
.glass-input {
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3)
    }
.grain-texture::after {
    content: "";
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0.035;
    pointer-events: none;
    background-image: url(https://lh3.googleusercontent.com/aida-public/AB6AXuAtafV0W40ANNyrkrxw6hAflYc92tF133AtOmoOKFTU-v3HkYxB1Its5yIXL24NMBsl91nU6859TkyS2tWw4p-XFUmCHQLqF2_N4wjDyIrXnUN-4m9CX6Ca67sAf9JlGBLWpW-pAzyLz-gPZGmImXc-ukQJRNHEErw18p26NQCCrN7AjOnEtHyjU56-3Py8YOnxXCsUMUnwPo2s_ZjMPE0FHhK8nXkqftBdNoBKiOOifkNG-6hKIf9lmiNeyL-BfDLOQo07AwuvPnM);
    z-index: 1
    }
@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(6deg);
        } 50% {
        transform: translateY(-15px) rotate(4deg);
        }
    }
@keyframes float-slow {
    0%, 100% {
        transform: translate(0, 0);
        } 33% {
        transform: translate(10px, -20px);
        } 66% {
        transform: translate(-15px, 10px);
        }
    }
.animate-float {
    animation: float 6s ease-in-out infinite
    }
.animate-float-slow {
    animation: float-slow 12s ease-in-out infinite
    }
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
        } to {
        opacity: 1;
        transform: translateY(0);
        }
    }
.reveal {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0
    }
.delay-1 {
    animation-delay: 0.1s
    }
.delay-2 {
    animation-delay: 0.2s
    }
.delay-3 {
    animation-delay: 0.3s
    }
.delay-4 {
    animation-delay: 0.4s
    }
.delay-5 {
    animation-delay: 0.5s
    }</style>
</head>
<body class="bg-surface font-body text-on-surface selection:bg-primary-container selection:text-on-primary-container overflow-x-hidden">
<main class="min-h-screen flex flex-col md:flex-row relative">
<!-- Left Side: Atmosphere -->
<section class="relative w-full md:w-1/2 min-h-[409px] md:min-h-screen flex items-center justify-center p-8 overflow-hidden signature-gradient grain-texture">
<!-- Multi-layered Background Accents -->
<div class="absolute top-[-10%] left-[-10%] w-96 h-96 rounded-full bg-primary-fixed-dim opacity-25 blur-3xl animate-float-slow"></div>
<div class="absolute bottom-[-15%] right-[-10%] w-[500px] h-[500px] rounded-full bg-secondary-fixed-dim opacity-20 blur-[120px]"></div>
<div class="absolute top-1/4 right-0 w-32 h-32 rounded-full border-[20px] border-white opacity-5"></div>
<div class="absolute top-1/2 left-1/4 w-12 h-12 bg-white/10 blur-sm rounded-full animate-pulse"></div>
<div class="relative z-10 max-w-lg text-center md:text-left space-y-6">
<h1 class="font-headline text-5xl md:text-7xl font-extrabold text-white leading-tight tracking-tighter reveal">
                    Adventure starts here
                </h1>
<p class="font-body text-lg md:text-xl text-on-primary opacity-90 max-w-md reveal delay-1">
                    Create an account to join our premium community.
                </p>
<!-- Atmospheric Graphic Element -->
<div class="pt-8 flex justify-center md:justify-start reveal delay-2">
<div class="relative w-72 h-72 rounded-3xl overflow-hidden shadow-2xl animate-float border-4 border-white/20">
<img class="w-full h-full object-cover brightness-95" data-alt="atmospheric photo of a wooden pier leading into calm blue water during sunrise with soft pastel colors" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB5aPpVsg8XHax70eKbWq2bjvxAEVE5raMOLN_7uPrI1h7lUKevZVCPndIOKEwiN30EIZ4e10FyIsgA_mTJIgGGyhqj1cpGFGLwgCWwLlCXPrjk7dcsxY4Rk4eN6gYRcQsGxR2Hba7t-F2EDD8kHs37REUkV5McxwdCBfxbwt75-5BJzZ9AjPAtYNrFaQR5CrEZvELsBYh_f-AozVe0HikSZco-UnSaJbIaYb7EmLjOm-2myfv88FplRJOYlpDSRdjEwQHgIdwWhYU"/>
</div>
</div>
</div>
</section>
<!-- Right Side: Precision -->
<section class="w-full md:w-1/2 flex items-center justify-center p-8 md:p-16 bg-surface-container-lowest relative grain-texture">
<!-- Advanced Lighting Effects & Parallax Elements -->
<div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_30%,rgba(45,73,220,0.06)_0%,transparent_50%)] z-0"></div>
<div class="absolute top-1/4 right-1/4 w-64 h-64 bg-primary/5 rounded-full blur-[100px] -z-0"></div>
<div class="absolute bottom-10 left-10 w-24 h-24 bg-secondary/10 rounded-full blur-2xl animate-float-slow -z-0"></div>
<div class="absolute top-20 right-20 w-16 h-16 border-2 border-primary/10 rounded-xl rotate-12 -z-0"></div>
<!-- Card Container with Enhanced Visual Depth -->
<div class="w-full max-w-md space-y-10 relative z-10 glass-card p-8 md:p-10 rounded-[2.5rem]">
<!-- Logo & Greeting -->
<div class="space-y-4 reveal">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl signature-gradient flex items-center justify-center shadow-lg shadow-primary/20">
<span class="material-symbols-outlined text-white text-2xl" style="font-variation-settings: 'FILL' 1;">explore</span>
</div>
<span class="font-headline text-2xl font-extrabold tracking-tight text-primary">Adventure</span>
</div>
<div class="pt-4">
<h2 class="font-headline text-3xl font-bold text-on-surface">Hello! Welcome back</h2>
<p class="text-on-surface-variant font-medium mt-1">Please enter your details to sign in</p>
</div>
</div>
<!-- Form -->
<form action="#" class="space-y-6 reveal delay-3">
<div class="space-y-5">
<!-- Email Field -->
<div class="space-y-2">
<label class="font-label text-sm font-semibold text-on-surface-variant px-1" for="email">Email Address</label>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined text-xl">mail</span>
</div>
<input class="block w-full pl-11 pr-4 py-4 glass-input rounded-2xl text-on-surface placeholder-outline/60 focus:ring-4 focus:ring-primary/10 focus:border-primary/30 focus:bg-white transition-all duration-300 outline-none" id="email" name="email" placeholder="name@example.com" type="email"/>
</div>
</div>
<!-- Password Field -->
<div class="space-y-2">
<label class="font-label text-sm font-semibold text-on-surface-variant px-1" for="password">Password</label>
<div class="relative group">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline group-focus-within:text-primary transition-colors">
<span class="material-symbols-outlined text-xl">lock</span>
</div>
<input class="block w-full pl-11 pr-12 py-4 glass-input rounded-2xl text-on-surface placeholder-outline/60 focus:ring-4 focus:ring-primary/10 focus:border-primary/30 focus:bg-white transition-all duration-300 outline-none" id="password" name="password" placeholder="••••••••" type="password"/>
<button class="absolute inset-y-0 right-0 pr-4 flex items-center text-outline hover:text-primary transition-colors" type="button">
<span class="material-symbols-outlined text-xl">visibility</span>
</button>
</div>
</div>
</div>
<!-- Options -->
<div class="flex items-center justify-between font-label text-sm">
<label class="flex items-center gap-2 cursor-pointer group">
<input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 transition-all cursor-pointer bg-white/50" type="checkbox"/>
<span class="text-on-surface-variant group-hover:text-on-surface transition-colors">Remember me</span>
</label>
<a class="text-primary font-bold hover:text-primary-dim transition-colors decoration-2 underline-offset-4" href="#">Reset Password!</a>
</div>
<!-- Submit -->
<button class="w-full py-4 rounded-full signature-gradient text-on-primary font-headline font-bold text-lg hover:scale-[1.02] hover:brightness-110 active:scale-95 transition-all duration-300 shadow-xl shadow-primary/25" type="submit">
                        Login
                    </button>
</form>
<!-- Separator -->
<div class="relative flex items-center py-2 reveal delay-4">
<div class="flex-grow border-t border-outline-variant/30"></div>
<span class="flex-shrink mx-4 text-outline font-label text-[10px] uppercase tracking-[0.2em] font-bold">Secure login with</span>
<div class="flex-grow border-t border-outline-variant/30"></div>
</div>
<!-- Social Logins -->
<div class="grid grid-cols-3 gap-4 reveal delay-4">
<a href="{{ route('google.login') }}" class="flex items-center justify-center py-3.5 glass-input rounded-xl hover:bg-white hover:shadow-lg hover:shadow-black/5 transition-all duration-300 group">
    <img alt="Google" class="w-5 h-5 group-hover:scale-110 transition-transform" src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png"/>
</a>
<a href="{{ route('facebook.login') }}" class="flex items-center justify-center py-3.5 glass-input rounded-xl hover:bg-white hover:shadow-lg hover:shadow-black/5 transition-all duration-300 group">
    <img alt="Facebook" class="w-6 h-6 group-hover:scale-110 transition-transform" src="https://cdn-icons-png.flaticon.com/512/124/124010.png"/>
</a>
<button class="flex items-center justify-center py-3.5 glass-input rounded-xl hover:bg-white hover:shadow-lg hover:shadow-black/5 transition-all duration-300 group">
<span class="material-symbols-outlined text-2xl text-on-surface group-hover:scale-110 transition-transform" style="font-variation-settings: 'FILL' 1;">ios</span>
</button>
</div>
<!-- Footer Link -->
<p class="text-center font-body text-on-surface-variant reveal delay-5">
                    Don't have an account? 
                    <a class="text-primary font-bold hover:text-primary-dim transition-colors decoration-2 underline-offset-4 ml-1" href="#">Create Account</a>
</p>
</div>
</section>
</main>
</body></html>