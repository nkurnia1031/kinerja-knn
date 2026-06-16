<form @submit.prevent="onSubmit" id="form-input" method="POST">

    <div class="card-body">
        <div class="row" v-if="fields!=''">
            <div v-for="i in fields2" class="col-12 col-lg-6 row">

                <div v-for="data in i" :class="'form-group col-12 col-lg-' + data.pnj">
                    <label class=" col-form-label ">@{{ data.label }}</label>
                    <template v-if="inArray(data.name,['ttdKetua'])">
                        <div class="w-100 d-flex  flex-column ">
                            <span>
                                <canvas id="ttdKetua" class="shadow-sm"></canvas>
                                <input v-model="ttdKetua2" type="text"
                                    style="width: 1px; height: 1px;position: absolute;left: 50%;
                                    top: 50%;z-index: -9;"
                                    required :id="data.name + '1'" :name="'input[' + data.name + ']'">

                            </span>
                            <span> <a onclick="app.ttdKetua.clear()" href="javascript:;"
                                    class="btn btn-sm btn-secondary">Clear</a></span>
                        </div>
                    </template>

                    {!! $tambahan !!}

                    <template v-else-if="inArray(data.name,['deskripsi','catatan'])">
                        <textarea :type="data.type" rows="2" :required="data.req" :maxlength="data.max"
                            :name="'input[' + data.name + ']'" class="form-control " v-model="data.val" :id="data.name + '1'"
                            :placeholder="data.label" :aria-describedby="data.name + '12'"></textarea>
                    </template>
                    <template v-else-if="inArray(data.name,['acara','keputusan'])">
                        <textarea rows="4"
                            style="width: 1px; height: 1px;position: absolute;left: 50%;
                        top: 50%;z-index: -9;"
                            :required="data.req" :maxlength="data.max" :name="'input[' + data.name + ']'"
                            class="form-control summernote  " v-model="data.val" :id="data.name + '1'" :placeholder="data.label"
                            :aria-describedby="data.name + '12'"></textarea>
                    </template>




                    <template v-else>
                        <input :type="data.type" step="any" v-model="data.val" :required="data.req"
                            :maxlength="data.max" :name="'input[' + data.name + ']'" class="form-control "
                            :id="data.name + '1'" :placeholder="data.label" :aria-describedby="data.name + '12'">
                    </template>
                    <small class="text-danger">@{{ data.small }}</small>
                </div>

            </div>
        </div>

    </div>
    <div class="card-footer d-flex flex-column mt-3">
        <div class="w-100 d-flex justify-content-end">
            <button type="reset" @click="form=false"
                class="btn  btn-secondary resetnya shadow text-white btn-sm me-2">Close</button>
            <template v-if="edit">

                <input type="hidden" name="key" :value="key">
                <button type="button" v-if="proses" disabled id="submitButton" name="aksi" value="update"
                    class="btn shadow btn-info disabled btn-sm text-white  mx-2">Proses...</button>
                <button type="submit" v-else id="submitButton" name="aksi" value="update"
                    class="btn shadow btn-info   text-white btn-sm mx-2">Simpan</button>
            </template>
            <template v-else>
                <button type="button" v-if="proses" disabled id="submitButton" name="aksi" value="update"
                    class="btn shadow btn-info disabled btn-sm text-white  mx-2">Proses...</button>
                <button type="submit" v-else id="submitButton" name="aksi" value="insert"
                    class="btn shadow btn-info   text-white btn-sm mx-2">Simpan</button>
            </template>


        </div>
    </div>
</form>
