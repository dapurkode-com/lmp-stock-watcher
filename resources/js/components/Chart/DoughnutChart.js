
import {Doughnut, mixins} from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
    extends: Doughnut,
    mixins: [reactiveProp],
    data(){
        return {
            options: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        fontColor:"white"
                    },
                    onClick: null
                },
                animation: {
                    animateRotate: false,
                    animateScale: true
                },
                responsive: true,
                tooltips: {
                    callbacks: {
                        title: (tooltipItems, data) => data.labels[tooltipItems[0].index],
                        label: (tooltipItems, data) => 'Summary: ' + data.datasets[0].data[tooltipItems.index]
                    }
                },
                legendCallback: chart => {
                    let html = '<ul>';
                    chart.data.labels.forEach((l, i) => {
                        const ds = chart.data.datasets[0];
                        const bgColor = ds.backgroundColor[i];
                        const border = ds.borderWidth + 'px solid ' + ds.borderColor[i];
                        html += '<li>' +
                            '<span style="width: 36px; height: 14px; background-color:' + bgColor + '; border:' + border + '" onclick="onLegendClicked(event, \'' + i + '\')">&nbsp;</span>' +
                            '<span id="legend-label-' + i + '" onclick="onLegendClicked(event, \'' + i + '\')">' +
                            (Array.isArray(l) ? l.join('<br/>') : l) +'</span>' +
                            '</li>';
                    });
                    return html + '</ul>';
                }
            }
        }
    },
    mounted () {
        this.renderChart(this.chartData, this.options)
    }
}
