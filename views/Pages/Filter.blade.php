<div class="col-lg-12 mb-3 col-md-12">
    <div class="card o-hidden  border-bottom-dark shadow  ">
        <div class="card-header align-items-center justify-content-between d-flex py-3">
            <span>
                <h6 class="m-0 font-weight-bold text-dark">Filter Data</h6>
            </span>
            <span>
            </span>
        </div>
        <form method="POST" @submit.prevent="onFilter" id="form-filter">

            <div class="card-body">
                <div class="row" v-if="filter!=''">
                    <div v-for="i in filter" class="col-12 col-lg-3 col-sm-6">
                        <div class=" form-group ">
                            <label class=" col-form-label ">@{{ i.label }}</label>
                            <template v-if="inArray(i.type,['date','datetime-local'])">
                                <input :name="i.name" type="daterange" class="form-control">
                                <small class="text-danger"></small>
                            </template>
                            <template v-else>
                                <select :name="i.name + '[]'" data-placeholder="Pilih" multiple
                                    class="select form-control">
                                    <option value=""></option>
                                    <option v-for="i2 in i.isi" :value="i2.key">@{{ i2.label }}
                                    </option>
                                </select>
                                <small class="text-danger"></small>
                            </template>
                        </div>
                    </div>


                </div>
                <div class="row">
                    <div class="col-12">
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-3 col-sm-6">
                        <div class=" form-group ">
                            <label class=" col-form-label ">Cari</label>
                            <input type="text" v-model="request.q" name="q" class="form-control">
                            <small class="text-danger"></small>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3 col-sm-6">
                        <div class=" form-group ">
                            <label class=" col-form-label ">Urut Berdasarkan</label>
                            <select name="SortBy" v-model="request.SortBy" data-placeholder="Pilih"
                                class="form-control select">
                                <option value=""></option>
                                <option v-for="i in fields" :value="i.name">@{{ i.label }}
                                </option>
                            </select>
                            <small class="text-danger"></small>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3 col-sm-6">
                        <div class=" form-group ">
                            <label class=" col-form-label ">Urut Dengan</label>
                            <select name="SortWith" v-model="request.SortWith" data-placeholder="Pilih"
                                class="form-control select">
                                <option value=""></option>
                                <option value="ASC">Naik (ASC)</option>
                                <option value="DESC">Turun (DESC)</option>
                            </select>
                            <small class="text-danger"></small>
                        </div>
                    </div>
                    <div class="col-12 col-lg-3 col-sm-6">
                        <div class=" form-group ">
                            <label class=" col-form-label ">Limit Data</label>
                            <input type="number" v-model="request.limit" name="limit" class="form-control">
                            <small class="text-danger"></small>
                        </div>
                    </div>
                </div>


            </div>
            <div class="card-footer d-flex flex-column mt-3">
                <div class="w-100 d-flex justify-content-end">
                    <button type="button" v-if="prosesFilter" disabled id="submitButton" name="aksi" value="update"
                        class="btn btn-sm shadow btn-primary disabled  text-white  mx-2">Proses...</button>
                    <button type="submit" v-else class="btn btn-sm btn-primary shadow text-white me-2">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>
