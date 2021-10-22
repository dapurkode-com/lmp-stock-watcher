<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title"><i class="fas fa-eye"></i> Watchlist</h3>
                            <small class="font-italic"> per troy ounce</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th class="text-right">Current</th>
                                    <th class="text-right">Change</th>
                                    <th class="text-right">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="commoditys.length == 0">
                                    <td colspan="5" class="text-center">No Data</td>
                                </tr>
                                <tr v-for="(commodity, index) in sortedcommoditys" :key="commodity.id">
                                    <td>{{ ++index }}</td>
                                    <th>{{ commodity.name }}</th>
                                    <td class="text-right">{{ commodity.current_price != null ? commodity.current_price.toLocaleString() : '-' }}</td>
                                    <td class="text-right">{{ commodity.change != null ? commodity.change.toLocaleString() : '-' }}</td>
                                    <td class="text-right"><span class="badge" :class="{'badge-success': commodity.change > 0, 'badge-secondary': commodity.change == 0, 'badge-danger': commodity.change < 0}">{{ commodity.percent_change != null ? commodity.percent_change.toLocaleString() : '-' }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Currency in United State Dollar (USD)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>

</style>

<script>
    export default {
        data() {
            return {
                commoditys: []
            }
        },
        created() {
            this.fetchcommoditys();
            this.listenForChanges();
        },
        methods: {
            fetchcommoditys() {
                axios.get('/api/commodity-list').then((response) => {
                    this.commoditys = response.data;
                })
            },
            listenForChanges() {
                Echo.channel('commodity')
                .listen('.CommodityEvent', (e) => {
                    console.log(e);
                    var commodity = this.commoditys.find((commodity) => commodity.id === e.id);
                        if(commodity){
                            var index = this.commoditys.indexOf(commodity);
                            this.commoditys[index].prev_price = e.prev_price;
                            this.commoditys[index].current_price = e.current_price;
                            this.commoditys[index].change = e.change;
                            this.commoditys[index].percent_change = e.percent_change;
                        }
                        else {
                            this.commoditys.push(e)
                        }
                    })
            }
        },
        computed: {
            sortedcommoditys() {
                function compare(a, b) {
                    if (a.name < b.name)
                        return -1;
                    if (a.name > b.name)
                        return 1;
                    return 0;
                }
                return this.commoditys.sort(compare)
            }
        }
    }

</script>
