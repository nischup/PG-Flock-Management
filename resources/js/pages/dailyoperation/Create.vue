<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import Datepicker from '@vuepic/vue-datepicker'
import { useAgeCalculator } from '@/composables/useAgeCalculator'
import '@vuepic/vue-datepicker/dist/main.css'
import { type BreadcrumbItem } from '@/types'
import FileUploader from '@/components/FileUploader.vue'

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Flock Management', href: '/flocks' },
  { title: 'Daily Operation', href: '' },
]

// Props
const props = defineProps<{
  flocks: Array<any>
  feeds?: Array<any>
}>()

// Tabs
const tabs = [
  { key: 'daily_mortality', label: 'Mortality' },
  { key: 'feed_consumption', label: 'Feed' },
  { key: 'water_consumption', label: 'Water' },
  { key: 'light_hour', label: 'Light' },
  { key: 'destroy', label: 'Destroy' },
  { key: 'cull', label: 'Cull' },
  { key: 'sexing_error', label: 'Sexing Error' },
  { key: 'weight', label: 'Weight' },
  { key: 'temperature', label: 'Temperature' },
  { key: 'humidity', label: 'Humidity' },
  { key: 'medicine', label: 'Medicine' },
  { key: 'vaccine', label: 'Vaccine' },
]

// Active Tab
const activeTabIndex = ref(0)
const activeTab = computed(() => tabs[activeTabIndex.value].key)

// Form
const form = useForm({
  flock_id: '',
  operation_date: new Date().toISOString().substr(0, 10),
  female_mortality: 0,
  male_mortality: 0,
  female_reason: '',
  male_reason: '',
  mortalitynote: '',
  feed_type_id: '',
  feed_quantity: 0,
  feed_unit: '',
  feed_note: '',
  water_consumption: 0,
  light_hour: 0,
  destroy: 0,
  cull: 0,
  sexing_error: 0,
  egg_collection: 0,
  weight: 0,
  temperature: 0,
  humidity: 0,
})

// Shed & flock info
const shedQty = ref({ opening: 0, current: 0 })
const flockInfo = ref<{ age: string }>({ age: '0 weeks 0 days' })

// Dummy data for shed info
const shedInfo = {
  1: { opening: 12000, current: 11500, start_date: '2025-07-12' },
  2: { opening: 11500, current: 11450, start_date: '2025-05-12' },
  3: { opening: 11000, current: 10000, start_date: '2025-06-12' },
}

// Dummy tab counts
const tabCountsData = {
  1: { daily_mortality: 10, feed_consumption: "200 Kg", water_consumption: "150 L", light_hour: "80 H", destroy: 5, cull: 3, sexing_error: 2, weight: "1600 gm", temperature: 28, humidity: 70, egg_collection: 9000 },
  2: { daily_mortality: 15, feed_consumption: "180 Kg", water_consumption: "130 L", light_hour: "80 H", destroy: 4, cull: 2, sexing_error: 1, weight: "1500 gm", temperature: 29, humidity: 65, egg_collection: 8900 },
  3: { daily_mortality: 25, feed_consumption: "190 Kg", water_consumption: "160 L", light_hour: "70 H", destroy: 6, cull: 5, sexing_error: 3, weight: "1400 gm", temperature: 27, humidity: 72, egg_collection: 8000 },
}

// Counts for dashboard
const counts = ref<Record<string, number | string>>({})
const progress = ref(65); 
// Watch flock change
watch(() => form.flock_id, (id) => {
  if (!id) {
    shedQty.value = { opening: 0, current: 0 }
    flockInfo.value.age = '0 weeks 0 days'
    counts.value = {}
    return
  }
  const data = shedInfo[id]
  shedQty.value = { opening: data.opening, current: data.current }
  counts.value = tabCountsData[id]
  flockInfo.value.age = useAgeCalculator(data.start_date)
})


// Navigation
function nextTab() { if (activeTabIndex.value < tabs.length - 1) activeTabIndex.value++ }
function prevTab() { if (activeTabIndex.value > 0) activeTabIndex.value-- }

// Submit
function submit() {
  form.post(route('daily-operations.store'), {
    onSuccess: () => form.reset()
  })
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Daily Operation" />

  <form @submit.prevent="submit" class="p-6 space-y-6">

    <!-- Flock Info -->
    <div class="border rounded-lg p-4 shadow-sm bg-white">
      <h2 class="font-semibold text-lg mb-4">Flock Information</h2>
      <div
      class="mb-4 h-6 rounded-xl bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 shadow-md flex items-center justify-end pr-2 text-black font-bold text-sm transition-all duration-500"
      :style="{ width: progress + '%' }"
    >
      {{ progress }}%
    </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Flock Select -->
        <div>
          <Label>Select Flock</Label>
          <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Flock</option>
            <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">
              {{ flock.flock_code }}
            </option>
          </select>
        </div>

        <!-- Date Picker -->
        <div>
          <Label>Date</Label>
          <Datepicker
            v-model="form.operation_date"
            format="yyyy-MM-dd"
            :input-class="'mt-2 border rounded px-3 py-2 w-full'"
            placeholder="Select Date"
            :auto-apply="true"
          />
        </div>

        <!-- Shed Info -->
        <div class="col-span-1 md:col-span-2 mt-2 border rounded shadow-sm bg-white p-1">
          <div class="grid grid-cols-3 gap-4 text-center">
            <div class="bg-yellow-100 p-4">
              <p class="text-gray-700 font-medium">Total Chicks</p>
              <p class="text-xl font-bold">{{ shedQty.opening }}</p>
            </div>
            <div class="bg-green-100 p-4">
              <p class="text-gray-700 font-medium">Current Chicks</p>
              <p class="text-xl font-bold">{{ shedQty.current }}</p>
            </div>
            <div class="bg-blue-100 p-4">
              <p class="text-gray-700 font-medium">Current Age</p>
              <p class="text-xl font-bold">{{ flockInfo.age }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Tabs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
      <div 
        v-for="(tab, index) in tabs" 
        :key="tab.key"
        @click="activeTabIndex = index"
        class="cursor-pointer p-6 border rounded-lg shadow text-center font-semibold transition-transform hover:scale-105"
        :class="activeTabIndex === index ? 'bg-chicken text-white' : 'bg-white text-gray-700'"
      >
        {{ tab.label }}
        <span v-if="counts[tab.key] !== undefined" class="block mt-2 text-black text-2xl font-bold">
          {{ counts[tab.key] }}
        </span>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="border rounded-lg p-4 shadow-sm mt-4 bg-white">

      <!-- Mortality Tab -->
      <div v-if="activeTab === 'daily_mortality'">
        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col">
            <Label>Female Chick Mortality</Label>
            <Input v-model.number="form.female_mortality" type="number"/>
          </div>
          <div class="flex flex-col">
            <Label>Male Chick Mortality</Label>
            <Input v-model.number="form.male_mortality" type="number"/>
          </div>
          <div class="flex flex-col">
            <Label>Female Reason</Label>
            <Input v-model="form.female_reason" type="text"/>
          </div>
          <div class="flex flex-col">
            <Label>Male Reason</Label>
            <Input v-model="form.male_reason" type="text"/>
          </div>
          <div class="flex flex-col col-span-2">
            <Label>Note</Label>
            <textarea v-model="form.mortalitynote" class="border rounded px-3 py-2"></textarea>
          </div>
        </div>
      </div>

      <!-- Feed Tab -->
      <div v-if="activeTab === 'feed_consumption'">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label>Feed Type</Label>
            <select v-model="form.feed_type_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Feed</option>
              <option v-for="feed in props.feeds" :key="feed.id" :value="feed.id">
                {{ feed.name }}
              </option>
            </select>
          </div>
          <div>
            <Label>Quantity</Label>
            <Input v-model.number="form.feed_quantity" type="number"/>
          </div>
          <div>
            <Label>Unit</Label>
            <Input v-model="form.feed_unit" type="text"/>
          </div>
          <div class="col-span-3 mt-2">
            <Label>Note</Label>
            <textarea v-model="form.feed_note" class="w-full border rounded px-3 py-2"></textarea>
          </div>
        </div>
      </div>

      <!-- Water Tab -->
      <div v-if="activeTab === 'water_consumption'">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label>Water</Label>
            <select v-model="form.water_type_id" class="w-full mt-1 border rounded px-3 py-2">
                <option value="">Select Water</option>
                <option v-for="water in props.waters" :key="water.id" :value="water.id">
                {{ water.name }}
                </option>
            </select>
            </div>

            <div>
            <Label>Quantity</Label>
            <Input v-model.number="form.water_quantity" type="number" />
            </div>
            <div class="col-span-3 mt-2">
            <Label>Note</Label>
            <textarea v-model="form.water_note" class="w-full border rounded px-3 py-2"></textarea>
            </div>
        </div>
      </div>

      <!-- Water Tab -->
      <div v-if="activeTab === 'light_hour'">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label>Light Hour</Label>
                <Input v-model.number="form.light_hour" type="number" />
            </div>
            <div>
                <Label>Note</Label>
                <textarea v-model="form.lighthour_note" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            </div>
      </div>


    <!-- destroy Tab -->
      <div v-if="activeTab === 'destroy'">

        <div class="grid grid-cols-2 gap-4">
            <!-- Destroy Male Quantity -->
            <div class="flex flex-col">
                <Label>Destroy Male Qty</Label>
                <Input v-model.number="form.destroy_male_qty" type="number" min="0" />
            </div>

            <!-- Destroy Male Reason -->
            <div class="flex flex-col">
                <Label>Destroy Male Reason</Label>
                <Input v-model="form.destroy_male_reason" type="text" />
            </div>

            <!-- Destroy Female Quantity -->
            <div class="flex flex-col">
                <Label>Destroy Female Qty</Label>
                <Input v-model.number="form.destroy_female_qty" type="number" min="0" />
            </div>

            <!-- Destroy Female Reason -->
            <div class="flex flex-col">
                <Label>Destroy Female Reason</Label>
                <Input v-model="form.destroy_female_reason" type="text" />
            </div>
            </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label>Note</Label>
            <textarea v-model="form.destroy_note" class="border rounded px-3 py-2"></textarea>
            </div>

        </div>


        <!-- culling Tab -->
      <div v-if="activeTab === 'cull'">

            <div class="grid grid-cols-2 gap-4">
                <!-- Cull Male Quantity -->
                <div class="flex flex-col">
                    <Label>Cull Male Qty</Label>
                    <Input v-model.number="form.cull_male_qty" type="number" min="0" />
                </div>

                <!-- Cull Male Reason -->
                <div class="flex flex-col">
                    <Label>Cull Male Reason</Label>
                    <Input v-model="form.cull_male_reason" type="text" />
                </div>

                <!-- Cull Female Quantity -->
                <div class="flex flex-col">
                    <Label>Cull Female Qty</Label>
                    <Input v-model.number="form.cull_female_qty" type="number" min="0" />
                </div>

                <!-- Cull Female Reason -->
                <div class="flex flex-col">
                    <Label>Cull Female Reason</Label>
                    <Input v-model="form.cull_female_reason" type="text" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label>Note</Label>
                <textarea v-model="form.cull_note" class="border rounded px-3 py-2"></textarea>
                </div>

        </div>

        <!-- sexxing error Tab -->
        <div v-if="activeTab === 'sexing_error'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Sexing Error Male Qty -->
                <div class="flex flex-col">
                    <Label>Sexing Error Male Qty</Label>
                    <Input v-model.number="form.sexing_error_male_qty" type="number" min="0" />
                </div>

                

                <!-- Sexing Error Female Qty -->
                <div class="flex flex-col">
                    <Label>Sexing Error Female Qty</Label>
                    <Input v-model.number="form.sexing_error_female_qty" type="number" min="0" />
                </div>
             </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label>Note</Label>
            <textarea v-model="form.sexing_error_note" class="border rounded px-3 py-2"></textarea>
            </div>  
        </div>


        <!-- weight Tab -->
        <div v-if="activeTab === 'weight'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Weight Male Qty -->
                <div class="flex flex-col">
                    <Label>Weight Male Qty</Label>
                    <Input v-model.number="form.weight_male_qty" type="number" min="0" />
                </div>

                <!-- Weight Female Qty -->
                <div class="flex flex-col">
                    <Label>Weight Female Qty</Label>
                    <Input v-model.number="form.weight_female_qty" type="number" min="0" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label>Note</Label>
                <textarea v-model="form.weight_note" class="border rounded px-3 py-2"></textarea>
                </div>

        </div>


        <!-- temperature Tab -->
        <div v-if="activeTab === 'temperature'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Inside Temperature -->
                <div class="flex flex-col">
                    <Label>Inside Temperature</Label>
                    <Input v-model.number="form.inside_temp" type="number" step="0.1" />
                </div>

                <!-- Std Inside Temperature -->
                <div class="flex flex-col">
                    <Label>Std Inside Temperature</Label>
                    <Input v-model.number="form.std_inside_temp" type="number" step="0.1" />
                </div>

                <!-- Outside Temperature -->
                <div class="flex flex-col">
                    <Label>Outside Temperature</Label>
                    <Input v-model.number="form.outside_temp" type="number" step="0.1" />
                </div>

                <!-- Std Outside Temperature -->
                <div class="flex flex-col">
                    <Label>Std Outside Temperature</Label>
                    <Input v-model.number="form.std_outside_temp" type="number" step="0.1" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label>Note</Label>
                <textarea v-model="form.temp_note" class="border rounded px-3 py-2"></textarea>
                </div>

        </div>


        <!-- humidity Tab -->
        <div v-if="activeTab === 'humidity'">
            <div class="grid grid-cols-2 gap-4">
            <!-- Today Humidity -->
            <div class="flex flex-col">
                <Label>Today Humidity (%)</Label>
                <Input v-model.number="form.today_humidity" type="number" step="0.1" />
            </div>

            <!-- Std Humidity -->
            <div class="flex flex-col">
                <Label>Std Humidity (%)</Label>
                <Input v-model.number="form.std_humidity" type="number" step="0.1" />
            </div>
            </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label>Note</Label>
            <textarea v-model="form.humidity_note" class="border rounded px-3 py-2"></textarea>
            </div>
        </div>


         <!-- medicine Tab -->
        <div v-if="activeTab === 'medicine'">

            <div class="grid grid-cols-2 gap-4">
                <!-- Medicine Name -->
                <div class="flex flex-col">
                    <Label>Medicine</Label>
                    <select v-model="form.medicine_id" class="w-full mt-1 border rounded px-3 py-2">
                    <option value="">Select Medicine</option>
                    <option v-for="medicine in props.medicines" :key="medicine.id" :value="medicine.id">
                        {{ medicine.name }}
                    </option>
                    </select>
                </div>

                <!-- Quantity -->
                <div class="flex flex-col">
                    <Label>Quantity</Label>
                    <Input v-model.number="form.medicine_quantity" type="number" min="0" />
                </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                <!-- Unit -->
                <div class="flex flex-col">
                    <Label>Unit</Label>
                    <Input v-model="form.medicine_unit" type="text" placeholder="ml / gm / tablet" />
                </div>

                <!-- Time -->
                <div class="flex flex-col">
                    <Label>Time</Label>
                    <Input v-model="form.medicine_time" type="time" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label>Note</Label>
                <textarea v-model="form.medicine_note" class="border rounded px-3 py-2"></textarea>
                </div>
            </div> 
            
            <div v-if="activeTab === 'vaccine'">

        
                <div class="grid grid-cols-2 gap-4">
                <!-- Vaccine Name -->
                <div class="flex flex-col">
                    <Label>Vaccine</Label>
                    <select v-model="form.vaccine_id" class="w-full mt-1 border rounded px-3 py-2">
                    <option value="">Select Vaccine</option>
                    <option v-for="vaccine in props.vaccines" :key="vaccine.id" :value="vaccine.id">
                        {{ vaccine.name }}
                    </option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <Label>Dose</Label>
                    <Input v-model.number="form.vaccine_dose" type="number" min="0" />
                </div>
                <!-- Unit -->
                <div class="flex flex-col">
                    <Label>Unit</Label>
                    <Input v-model="form.vaccine_unit" type="text" placeholder="ml / dose" />
                </div>
                <!-- File Upload -->
                 <div class="flex flex-col"></div>
                <Label>Upload File</Label>
                <input 
                    type="file" 
                    @change="form.vaccine_file = $event.target.files[0]" 
                    class="w-full border rounded px-3 py-2 mt-1"
                />
                </div>

               
                


                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label>Note</Label>
                <textarea v-model="form.vaccine_note" class="border rounded px-3 py-2"></textarea>
                </div>
     
            
</div> 
            
    </div>

    <!-- Navigation -->
    <div class="flex justify-between mt-4">
      <Button type="button" @click="prevTab" :disabled="activeTabIndex === 0">Previous</Button>
      <Button v-if="activeTabIndex < tabs.length - 1" type="button" @click="nextTab">Next</Button>
      <Button v-else type="submit" class="bg-chicken">Save</Button>
    </div>

  </form>
</AppLayout>
</template>
