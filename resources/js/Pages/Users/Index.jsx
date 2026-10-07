import React, { useEffect, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

const emptyUser = { name: '', email: '', role: 'AUDITOR', password: '', password_confirmation: '' };

export default function Index({ users, currentUserId }) {
    const [editing, setEditing] = useState(null);
    const form = useForm(emptyUser);

    useEffect(() => {
        form.setData(editing
            ? { name: editing.name, email: editing.email, role: editing.role, password: '', password_confirmation: '' }
            : emptyUser);
        form.clearErrors();
    }, [editing]);

    const submit = event => {
        event.preventDefault();
        if (editing) {
            form.put(`/users/${editing.id}`, { onSuccess: () => setEditing(null) });
        } else {
            form.post('/users', { onSuccess: () => form.reset() });
        }
    };

    const remove = user => {
        if (window.confirm(`Hapus akun ${user.name}?`)) router.delete(`/users/${user.id}`);
    };

    return <AppLayout title="Manajemen Pengguna">
        <form onSubmit={submit} className="card mb-5 grid gap-4 md:grid-cols-2">
            <div><label>Nama</label><input className="input" required maxLength="255" value={form.data.name} onChange={e => form.setData('name', e.target.value)} /></div>
            <div><label>Email</label><input className="input" type="email" required value={form.data.email} onChange={e => form.setData('email', e.target.value)} /></div>
            <div><label>Peran</label><select className="input" value={form.data.role} onChange={e => form.setData('role', e.target.value)}><option value="AUDITOR">Auditor</option><option value="ADMIN">Admin</option></select></div>
            <div><label>{editing ? 'Password baru (kosongkan jika tidak diubah)' : 'Password'}</label><input className="input" type="password" minLength="8" required={!editing} autoComplete="new-password" value={form.data.password} onChange={e => form.setData('password', e.target.value)} /></div>
            <div><label>Konfirmasi Password</label><input className="input" type="password" minLength="8" required={!editing && !!form.data.password} autoComplete="new-password" value={form.data.password_confirmation} onChange={e => form.setData('password_confirmation', e.target.value)} /></div>
            <div className="flex items-end gap-2"><button className="btn-primary" disabled={form.processing}>{editing ? 'Simpan Perubahan' : 'Tambah Pengguna'}</button>{editing && <button className="btn" type="button" onClick={() => setEditing(null)}>Batal</button>}</div>
            {Object.keys(form.errors).length > 0 && <ul className="text-red-600 md:col-span-2">{Object.values(form.errors).map((error, index) => <li key={index}>{error}</li>)}</ul>}
        </form>

        <div className="card overflow-x-auto"><table className="w-full text-sm">
            <thead><tr className="border-b text-left"><th className="py-2">Nama</th><th>Email</th><th>Peran</th><th></th></tr></thead>
            <tbody>{users.map(user => <tr className="border-b" key={user.id}>
                <td className="py-3">{user.name}{user.id === currentUserId ? ' (Anda)' : ''}</td>
                <td>{user.email}</td>
                <td>{user.role === 'ADMIN' ? 'Admin' : 'Auditor'}</td>
                <td className="text-right whitespace-nowrap"><button type="button" className="btn mr-2" onClick={() => setEditing(user)}>Edit</button>{user.id !== currentUserId && <button type="button" className="btn" onClick={() => remove(user)}>Hapus</button>}</td>
            </tr>)}</tbody>
        </table></div>
    </AppLayout>;
}
