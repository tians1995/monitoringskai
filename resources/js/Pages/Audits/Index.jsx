import React, { useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function Index({ audits }) {
    const [editing, setEditing] = useState(null);
    const form = useForm({ name: '', year: new Date().getFullYear() });

    useEffect(() => {
        if (editing) form.setData({ name: editing.name, year: editing.year });
        else form.setData({ name: '', year: new Date().getFullYear() });
    }, [editing]);

    const submit = (event) => {
        event.preventDefault();
        if (editing) form.put(`/audits/${editing.id}`, { onSuccess: () => setEditing(null) });
        else form.post('/audits', { onSuccess: () => form.reset('name') });
    };

    return <AppLayout title="Daftar Audit">
        <form onSubmit={submit} className="card mb-5 grid gap-4 md:grid-cols-[1fr_180px_auto] md:items-end">
            <div><label>Nama Audit</label><input className="input" required maxLength="255" value={form.data.name} onChange={e => form.setData('name', e.target.value)} /></div>
            <div><label>Tahun</label><input className="input" type="number" min="1900" max="2200" required value={form.data.year} onChange={e => form.setData('year', e.target.value)} /></div>
            <div className="flex gap-2"><button className="btn-primary" disabled={form.processing}>{editing ? 'Simpan Perubahan' : 'Tambah Audit'}</button>{editing && <button type="button" className="btn" onClick={() => setEditing(null)}>Batal</button>}</div>
            {Object.values(form.errors).length > 0 && <p className="text-red-600 md:col-span-3">{Object.values(form.errors).join(' ')}</p>}
        </form>
        <div className="card overflow-x-auto"><table className="w-full text-sm"><thead><tr className="border-b text-left"><th className="py-2">Nama Audit</th><th>Tahun</th><th></th></tr></thead><tbody>{audits.map(audit => <tr className="border-b" key={audit.id}><td className="py-3">{audit.name}</td><td>{audit.year}</td><td className="text-right"><button className="btn" type="button" onClick={() => setEditing(audit)}>Edit</button></td></tr>)}</tbody></table></div>
    </AppLayout>;
}
