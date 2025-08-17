<script setup lang="ts">
import ApexChart from "vue3-apexcharts";
import { ref, watch } from "vue";
import type { ApexOptions } from "apexcharts";

interface ChartProps {
  series: { name: string; data: number[] }[];
  labels?: string[];
  type?: "line" | "bar" | "area" | "pie" | "donut" | "radialBar" | "scatter";
  title?: string;
}

const props = defineProps<ChartProps>();

const chartOptions = ref<ApexOptions>({
  chart: {
    id: "vuechart",
    toolbar: { show: true },
    zoom: { enabled: true },
  },
  xaxis: {
    categories: props.labels ?? [],
  },
  title: {
    text: props.title ?? "",
    align: "left",
    style: { fontSize: "16px" },
  },
  dataLabels: { enabled: false },
  tooltip: { enabled: true },
});

const series = ref(props.series);

watch(() => props.series, (newSeries) => {
  series.value = newSeries;
});
</script>

<template>
  <div class="bg-white p-4 rounded-lg shadow">
    <ApexChart
      :options="chartOptions"
      :series="series"
      :type="props.type ?? 'line'"
      height="350"
    />
  </div>
</template>
