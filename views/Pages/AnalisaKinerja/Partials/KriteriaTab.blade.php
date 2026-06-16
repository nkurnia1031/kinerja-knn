<div class="col-12" v-show="activeTab === 'kriteria' && !isLoading">
    <div class="card shadow mb-4" v-if="kriteria">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>Kriteria Penilaian Kinerja (@{{ kriteria.length }} Kriteria)
            </h6>
            <span class="badge" :class="totalBobot == 100 ? 'badge-success' : 'badge-warning'">
                Total Bobot: @{{ totalBobot }}%
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="60">Kode</th>
                            <th>Kriteria (Bahasa Indonesia)</th>
                            <th>Kriteria (English)</th>
                            <th class="text-center" width="100">Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="k in kriteria" :key="k.id">
                            <td class="text-center"><span class="badge badge-secondary">@{{ k.kode }}</span></td>
                            <td>@{{ k.nama }}</td>
                            <td class="text-muted">@{{ k.nama_en }}</td>
                            <td class="text-center"><span class="badge badge-info">@{{ k.bobot }}%</span></td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="3" class="text-right">Total Bobot:</td>
                            <td class="text-center">
                                <span class="badge" :class="totalBobot == 100 ? 'badge-success' : 'badge-danger'">
                                    @{{ totalBobot }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <h6 class="font-weight-bold mt-4">Skala Penilaian:</h6>
            <div class="row">
                <div class="col-md-3 mb-2"><div class="card bg-danger text-white"><div class="card-body text-center py-2"><h4 class="mb-0">1</h4><small>Kurang Baik</small></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-warning"><div class="card-body text-center py-2"><h4 class="mb-0">2</h4><small>Cukup</small></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-primary text-white"><div class="card-body text-center py-2"><h4 class="mb-0">3</h4><small>Baik</small></div></div></div>
                <div class="col-md-3 mb-2"><div class="card bg-success text-white"><div class="card-body text-center py-2"><h4 class="mb-0">4</h4><small>Sangat Baik</small></div></div></div>
            </div>
        </div>
    </div>
</div>
