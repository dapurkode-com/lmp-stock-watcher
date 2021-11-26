
import {Doughnut, mixins} from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
    extends: Doughnut,
    mixins: [reactiveProp],
    data(){
        return {
            options: {
                legend: {
                    position: 'bottom',
                    labels: {
                        fontColor:"white"
                    }
                },
                animation: {
                    animateRotate: false,
                    animateScale: true
                }
            }
        }
    },
    mounted () {
        this.renderChart(this.chartData, this.options)
    }
}
