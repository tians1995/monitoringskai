import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';

export default function Show({ finding }) {
    const update = useForm({ status: finding.computed_status, progress: finding.progress, notes: '', new_target_date: finding.target_date, version: finding.version });
    const evidence = useForm({ file: null });
    const items = finding.items?.length ? finding.items : (finding.temuan ? [{ id: 'legacy', description: finding.temuan }] : []);
    const submitUpdate = event => { event.preventDefault(); update.post(`/findings/${finding.id}/progress`); };

    return <AppLayout title={finding.judul_temuan || finding.no_lha}>
        <div className="grid md:grid-cols-3 gap-6">
            <div className="md:col-span-2 space-y-6">
                <div className="card">
                    <div className="finding-title-block"><div><small>No LHA {finding.no_lha}</small><h2>{finding.judul_temuan || 'Temuan Audit'}</h2></div><Link href={`/findings/${finding.id}/edit`} className="btn">Edit Temuan</Link></div>
                    <div className="grid md:grid-cols-3 gap-4 mb-5">
                        <div><small>Audit</small><div className="font-semibold">{finding.audit?.name} ({finding.audit?.year})</div></div>
                        <div><small>Rating</small><div className="font-semibold">{finding.rating}</div></div>
                        <div><small>Status</small><div className="font-semibold">{finding.computed_status}</div></div>
                        <div><small>PIC</small><div>{finding.pic_name || finding.pic?.name || '-'}</div></div>
                        <div><small>Target</small><div>{finding.target_date || '-'}</div></div>
                        <div><small>Progress</small><div>{finding.progress}%</div></div>
                    </div>
                    <section className="mb-5"><h3 className="font-bold mb-2">Rincian Temuan ({items.length})</h3>
                        {items.length ? <ol className="finding-detail-list">{items.map((item, index) => <li key={item.id || index}><span>{index + 1}</span><div className="whitespace-pre-wrap">{item.description}</div></li>)}</ol> : <p className="text-slate-500">Belum ada rincian temuan.</p>}
                    </section>
                    {[['Rekomendasi', finding.rekomendasi], ['Action Plan Unit Kerja', finding.action_plan]].map(([label, value]) => <section key={label} className="mb-5"><h3 className="font-bold mb-1">{label}</h3><div className="whitespace-pre-wrap text-slate-700">{value || '-'}</div></section>)}
                </div>
                <div className="card"><h2 className="font-bold mb-4">Timeline Monitoring</h2>{finding.updates.map(item => <div className="border-l-2 border-blue-300 pl-4 mb-4" key={item.id}><div className="font-semibold">{item.status} · {item.progress}%</div><div className="text-sm text-slate-500">{item.created_at} · {item.user?.name || '-'}</div><div>{item.notes}</div>{item.new_target_date && <div className="text-sm">Target: {item.new_target_date}</div>}</div>)}</div>
            </div>
            <aside className="card h-fit"><h2 className="font-bold mb-4">Update Monitoring</h2>
                <form onSubmit={submitUpdate}><label>Status</label><select className="input" value={update.data.status} onChange={event => update.setData('status', event.target.value)}><option value="open">open</option><option value="overdue">overdue</option><option value="closed">closed</option></select><label>Progress</label><input className="input" type="number" min="0" max="100" value={update.data.progress} onChange={event => update.setData('progress', event.target.value)}/><label>Target Baru</label><input className="input" type="date" value={update.data.new_target_date || ''} onChange={event => update.setData('new_target_date', event.target.value)}/><label>Catatan</label><textarea className="input" value={update.data.notes} onChange={event => update.setData('notes', event.target.value)}/><button className="btn-primary w-full">Update</button></form>
                <hr className="my-6"/><h2 className="font-bold mb-3">Evidence</h2><form onSubmit={event => { event.preventDefault(); evidence.post(`/findings/${finding.id}/evidence`); }}><input type="file" onChange={event => evidence.setData('file', event.target.files[0])}/><button className="btn mt-3">Upload</button></form>{finding.evidences.map(item => <div className="text-sm mt-2" key={item.id}>{item.file_name}</div>)}
            </aside>
        </div>
    </AppLayout>;
}
