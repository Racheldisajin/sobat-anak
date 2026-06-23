
<style>
/* SobatAnak Auth Permanent Inline Fix
   Supaya halaman login/register tetap rapi walaupun public/css tertimpa patch lain. */
.auth-split-section,
.auth-split-section *{box-sizing:border-box}
.auth-split-section{position:relative;min-height:calc(100vh - 90px);padding:4.5rem 1.25rem;display:flex;align-items:center;justify-content:center;overflow:hidden;background:linear-gradient(135deg,#F6FCFB 0%,#FFFFFF 45%,#FDECEA 100%);font-family:'Baloo 2',system-ui,sans-serif;color:#2A3D3C}
.auth-split-register{background:linear-gradient(135deg,#FDECEA 0%,#FFFFFF 46%,#D0F0ED 100%)}
.auth-split-orb{position:absolute;border-radius:999px;filter:blur(2px);opacity:.38;animation:authOrbMove 7s ease-in-out infinite;pointer-events:none}
.auth-split-orb-one{width:160px;height:160px;background:#4BBFB0;left:8%;top:12%}
.auth-split-orb-two{width:110px;height:110px;background:#E8756A;right:10%;bottom:16%;animation-delay:1.4s}
@keyframes authOrbMove{0%,100%{transform:translate3d(0,0,0) scale(1)}50%{transform:translate3d(18px,-20px,0) scale(1.06)}}
.auth-split-card{position:relative;width:min(980px,100%);min-height:520px;background:rgba(255,255,255,.95);border:1px solid rgba(212,238,236,.95);border-radius:2rem;box-shadow:0 28px 75px rgba(42,61,60,.13),0 18px 40px rgba(232,117,106,.10);overflow:hidden;display:grid;grid-template-columns:1.08fr .92fr;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}
.auth-split-card-register{grid-template-columns:.92fr 1.08fr}
.auth-animate-in{animation:authCardIn .46s cubic-bezier(.2,.8,.2,1) both}
@keyframes authCardIn{from{opacity:0;transform:translateY(18px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}
.auth-split-form-panel{padding:4rem 4.1rem;display:flex;flex-direction:column;justify-content:center;background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,255,255,.92));animation:authFormIn .46s cubic-bezier(.2,.8,.2,1) both}
.auth-register-form-panel{animation-name:authFormInRight}
@keyframes authFormIn{from{opacity:0;transform:translateX(-18px)}to{opacity:1;transform:translateX(0)}}
@keyframes authFormInRight{from{opacity:0;transform:translateX(18px)}to{opacity:1;transform:translateX(0)}}
.auth-mini-badge{width:max-content;background:#D0F0ED;color:#3AA89A;border:1px solid rgba(75,191,176,.35);border-radius:999px;padding:.38rem .8rem;font-size:.72rem;font-weight:1000;text-transform:uppercase;letter-spacing:.09em;margin-bottom:1rem}
.auth-split-title{font-size:clamp(2.15rem,4vw,3.55rem);line-height:1;font-weight:700;color:#2A3D3C;margin:0;font-family:'Fredoka',system-ui,sans-serif;letter-spacing:.01em}
.auth-split-subtitle{color:#6B8A88;font-weight:800;font-size:.96rem;line-height:1.55;margin:.8rem 0 0;max-width:370px}
.auth-modern-form{display:grid;gap:1rem;margin-top:1.65rem}
.auth-field-wrap{position:relative;display:flex!important;align-items:center;background:#fff;border:1.5px solid #D4EEEC;border-radius:999px;box-shadow:0 10px 28px rgba(75,191,176,.10);transition:.25s ease;overflow:hidden;margin:0}
.auth-field-wrap:focus-within{border-color:#4BBFB0;box-shadow:0 0 0 5px rgba(75,191,176,.13),0 14px 34px rgba(75,191,176,.14);transform:translateY(-1px)}
.auth-field-icon{width:3.05rem;min-width:3.05rem;display:flex;align-items:center;justify-content:center;font-size:.95rem;filter:saturate(1.06)}
.auth-field-wrap input{width:100%;border:0!important;outline:0!important;background:transparent!important;padding:.95rem 1.1rem .95rem 0!important;font-weight:900;color:#2A3D3C;font-family:inherit;box-shadow:none!important}
.auth-field-wrap input::placeholder{color:#9BB4B2;font-weight:900}
.auth-modern-form textarea,.auth-modern-form select{font-family:inherit}
.auth-form-options{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:-.15rem;font-size:.78rem;font-weight:900;color:#6B8A88}
.auth-form-options a,.auth-switch-text a{color:#E8756A;text-decoration:none;font-weight:1000;transition:.2s}
.auth-form-options a:hover,.auth-switch-text a:hover{color:#D05A50;text-decoration:underline;text-underline-offset:3px}
.auth-remember{display:flex;align-items:center;gap:.4rem;cursor:pointer;white-space:nowrap}
.auth-remember input{accent-color:#4BBFB0}
.auth-submit-btn{margin-top:.25rem;border:0;border-radius:999px;background:linear-gradient(135deg,#E8756A,#F5A05A);color:white;padding:.95rem 1.2rem;font-weight:1000;text-transform:uppercase;letter-spacing:.04em;box-shadow:0 12px 30px rgba(232,117,106,.28);cursor:pointer;transition:.25s ease;text-align:center;font-family:inherit}
.auth-submit-btn:hover{transform:translateY(-2px);box-shadow:0 18px 42px rgba(232,117,106,.33);filter:saturate(1.04)}
.auth-switch-text{text-align:center;margin-top:1.2rem;color:#6B8A88;font-weight:900;font-size:.86rem}
.auth-alert-error{margin-top:1.1rem;border:1px solid #F3C6C1;background:#FFF2F0;color:#D05A50;border-radius:1.1rem;padding:.85rem 1rem;font-weight:900;font-size:.88rem}
.auth-alert-soft{margin-top:1rem;border:1px solid rgba(75,191,176,.35);background:#E8F5F4;color:#3AA89A;border-radius:1rem;padding:.85rem 1rem;font-weight:900;font-size:.86rem}
.auth-split-welcome-panel{position:relative;padding:4rem 3.25rem;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:white;background:linear-gradient(145deg,#4BBFB0 0%,#72D8CC 42%,#E8756A 100%);overflow:hidden;isolation:isolate;animation:authWelcomeIn .46s cubic-bezier(.2,.8,.2,1) .04s both}
.auth-register-welcome{background:linear-gradient(145deg,#E8756A 0%,#F5A05A 42%,#4BBFB0 100%);animation-name:authWelcomeInLeft}
@keyframes authWelcomeIn{from{opacity:0;transform:translateX(22px)}to{opacity:1;transform:translateX(0)}}
@keyframes authWelcomeInLeft{from{opacity:0;transform:translateX(-22px)}to{opacity:1;transform:translateX(0)}}
.auth-split-welcome-panel:before{content:"";position:absolute;inset:1rem;border:1px solid rgba(255,255,255,.32);border-radius:1.5rem;z-index:-1}
.auth-split-welcome-panel:after{content:"";position:absolute;width:310px;height:310px;border-radius:999px;background:rgba(255,255,255,.16);right:-120px;top:-95px;z-index:-1;box-shadow:-260px 250px 0 rgba(255,255,255,.10)}
.auth-split-welcome-panel h2{font-size:clamp(2rem,4vw,3.2rem);font-weight:1000;line-height:1.05;margin:0 0 1rem;text-shadow:0 8px 20px rgba(42,61,60,.12)}
.auth-split-welcome-panel p{max-width:315px;line-height:1.55;font-weight:800;color:rgba(255,255,255,.93);margin:0}
.auth-floating-dot{position:absolute;font-size:1.5rem;opacity:.62;filter:drop-shadow(0 10px 16px rgba(42,61,60,.18));animation:authFloat 4.6s ease-in-out infinite}
.auth-floating-dot.dot-a{left:11%;top:19%}
.auth-floating-dot.dot-b{right:13%;top:26%;animation-delay:.8s}
.auth-floating-dot.dot-c{left:18%;bottom:20%;animation-delay:1.4s}
@keyframes authFloat{0%,100%{transform:translateY(0) rotate(-4deg)}50%{transform:translateY(-13px) rotate(5deg)}}
.auth-page-fast{padding-top:3.25rem!important;animation:authPageFade .26s ease both}
@keyframes authPageFade{from{opacity:.55;transform:translateY(8px)}to{opacity:1;transform:none}}
.auth-submit-btn.is-loading,.auth-resend-form button.is-loading{pointer-events:none;opacity:.82;transform:none!important}
.auth-submit-btn.is-loading:before,.auth-resend-form button.is-loading:before{content:"";width:16px;height:16px;border-radius:999px;border:2px solid rgba(255,255,255,.55);border-top-color:#fff;display:inline-block;margin-right:.45rem;vertical-align:-3px;animation:authSpin .7s linear infinite}
@keyframes authSpin{to{transform:rotate(360deg)}}
.auth-code-wrap input{letter-spacing:.24em;text-align:center;font-size:1.25rem;font-weight:1000}
.auth-resend-form{margin-top:.75rem;text-align:center}
.auth-resend-form button{border:0;background:#E8F5F4;color:#3AA89A;border-radius:999px;padding:.75rem 1.1rem;font-weight:1000;transition:.22s;cursor:pointer;font-family:inherit}
.auth-resend-form button:hover{background:#D0F0ED;transform:translateY(-1px)}
header.sticky{z-index:99999!important;position:sticky!important;top:0!important;background:rgba(255,255,255,.94)!important;box-shadow:0 10px 28px rgba(42,61,60,.06)}
@media(max-width:860px){.auth-split-section{padding:2rem 1rem}.auth-split-card,.auth-split-card-register{grid-template-columns:1fr;min-height:auto}.auth-split-form-panel{padding:2.2rem 1.45rem;order:1}.auth-split-welcome-panel{padding:2.6rem 1.4rem;min-height:260px;order:2}.auth-split-card-register .auth-split-welcome-panel{order:2}.auth-split-card-register .auth-split-form-panel{order:1}.auth-form-options{align-items:flex-start;flex-direction:column}.auth-split-title{font-size:2.25rem}.auth-split-subtitle{max-width:100%}}
@media(max-width:640px){.auth-page-fast{padding-top:1.4rem!important}.auth-split-welcome-panel{display:none}.auth-split-card{border-radius:1.5rem}}
</style>
