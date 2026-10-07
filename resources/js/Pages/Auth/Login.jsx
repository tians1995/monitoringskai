import React from 'react';
import { useForm } from '@inertiajs/react';
import { SafetyCertificateOutlined } from '@ant-design/icons';

export default function Login() {
  const form = useForm({ email: 'admin@audit.local', password: 'password', remember: true });
  const submit = event => { event.preventDefault(); form.post('/login'); };

  return <div className="login-page">
    <section className="login-showcase">
      <div className="login-brand"><span className="brand-mark"><SafetyCertificateOutlined /></span><span>Audit Finding Monitoring</span></div>
      <div className="login-showcase-copy"><h2>Temukan insight.<br />Tuntaskan tindak lanjut.</h2><p>Kelola temuan audit, pantau progres, dan koordinasikan tindak lanjut dalam satu dashboard.</p></div>
      <div className="login-showcase-foot">Audit Finding Monitoring · {new Date().getFullYear()}</div>
    </section>
    <main className="login-panel"><form className="login-form-wrap" onSubmit={submit}>
      <h1>Selamat datang</h1><p>Masuk untuk melanjutkan ke dashboard monitoring.</p>
      <label htmlFor="email">Email</label><input id="email" type="email" autoComplete="username" className="input" value={form.data.email} onChange={event => form.setData('email', event.target.value)} />
      {form.errors.email && <p className="text-red-600 text-sm mb-3">{form.errors.email}</p>}
      <label htmlFor="password">Password</label><input id="password" type="password" autoComplete="current-password" className="input" value={form.data.password} onChange={event => form.setData('password', event.target.value)} />
      {form.errors.password && <p className="text-red-600 text-sm mb-3">{form.errors.password}</p>}
      <button className="btn-primary w-full" disabled={form.processing}>{form.processing ? 'Memproses…' : 'Masuk ke dashboard'}</button>
    </form></main>
  </div>;
}
