<script setup>
import { ref } from "vue";
import { useGlobalState } from '@/store';
import { useFetch, useEventBus, useDebounceFn, useStorage } from '@vueuse/core';

// Define variables
const store = useGlobalState();
const stationName = ref(``);
const stationId = ref(null);
const stations = ref([]);
const stationsList = ref([]);
const randomTrain = ref(null);
const randomStop = ref(null);
const journeysStorage = useStorage(`journeys`, []);

// Lookup stations on transport.opendata.ch
const searchStations = async () => {
  stationsList.value = [];
  if (stationName.value.length <= 2) {
    return;
  }

  const { data, error } = await useFetch(`${location.protocol}//transport.opendata.ch/v1/locations?query=${stationName.value}&type=station`, { updateDataOnError: true });
  if (error.value) {
    try { data.value = JSON.parse(data.value)?.error } catch (err) { }
    return useEventBus("notify").emit(data.value || error.value, "bad");
  }

  if (data.value.length == 0) {
    return;
  }

  stations.value = JSON.parse(data.value);

  // Only for train stations
  stations.value.stations.forEach((sta) => {
    if (sta.icon == `train`) {
      stationsList.value.push({ id: sta.id, name: sta.name });
    }
  });
};

const debouncedStationSearch = useDebounceFn(searchStations, 500);

// Apply selected station
function selectStation(sta) {
  stationName.value = sta.name;
  stationId.value = sta.id;
  stationsList.value = [];
}

// Get random train on selected station from api
const getRandomTrain = async () => {
  const { data, error } = await useFetch(`${store.urls.app_api}/randomTrain.php?station=${stationId.value}`, { updateDataOnError: true });
  if (error.value) {
    try { data.value = JSON.parse(data.value)?.error } catch (err) { }
    return useEventBus("notify").emit(data.value || error.value, "bad");
  }
  randomTrain.value = JSON.parse(data.value);
};

// Get random stop from pass list
function getRandomStop() {
  const randomIndex = Math.floor(Math.random() * randomTrain.value.passList.length);
  const randomStation = randomTrain.value.passList[randomIndex];
  const position = randomIndex + 1;

  randomStop.value = {
    id: randomStation.id,
    name: randomStation.name,
    arrival: randomStation.arrival,
    stops: position,
  }
}

// Reset entire form
function reset() {
  stationName.value = ``;
  stationId.value = null;
  randomTrain.value = null;
  randomStop.value = null;
}

function saveJourney() {
  journeysStorage.value.push({
    from: {
      station_id: randomTrain.value.station_id,
      station_name: randomTrain.value.station_name,
      departure: randomTrain.value.departure,
    },
    to: {
      station_id: randomStop.value.id,
      station_name: randomStop.value.name,
      arrival: randomStop.value.arrival,
    },
  });

  stationName.value = randomStop.value.name;
  stationId.value = randomStop.value.id;

  randomTrain.value = null;
  randomStop.value = null;
}
</script>

<template>

  <!-- Search input -->
  <div v-if="!randomTrain" class="search-box">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input type="text" name="stationName" v-model="stationName" placeholder="Abfahrts Bahnhof"
      @input="debouncedStationSearch" required>
  </div>

  <!-- Search results -->
  <ul v-if="stationsList.length > 0">
    <li v-for="(station, i) in stationsList" :key="i" @click="selectStation(station)">{{ station.name }}</li>
  </ul>

  <!-- Dice button -->
  <button v-if="!randomTrain" class="action-btn primary" @click="getRandomTrain()">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="17 1 21 5 17 9"></polyline>
      <path d="M3 11V9a4 4 0 014-4h14"></path>
      <polyline points="7 23 3 19 7 15"></polyline>
      <path d="M21 13v2a4 4 0 01-4 4H3"></path>
    </svg>
    Würfeln
  </button>

  <!-- Information on random train -->
  <div v-if="randomTrain" class="card">
    {{ randomTrain.train_category }}{{ randomTrain.train_number }} - Richtung {{ randomTrain.to }}<br>
    um {{ randomTrain.departure }} Uhr<br>
    ab Gleis {{ randomTrain.platform }}<br>
    <span v-if="randomTrain.passList.length > 1">{{ randomTrain.passList.length }} mögliche Stops</span>
    <span v-else style="color: red;">1 möglicher Stop</span>
  </div>

  <!-- Action buttons -->
  <div v-if="randomTrain && !randomStop" class="button-wrapper">
    <button class="option-btn" @click="reset()">Zurücksetzen</button>
    <button class="option-btn" @click="getRandomTrain()">Erneut Würfeln</button>
    <button class="option-btn active" @click="getRandomStop()">Bestätigen</button>
  </div>

  <!-- Information on random stop -->
  <div v-if="randomStop" class="card">
    <span v-if="randomStop.stops > 1">{{ randomStop.stops }} Stationen</span>
    <span v-else style="color: red;">1 Station</span>
    bis {{ randomStop.name }}<br>
    Ankunft um {{ randomStop.arrival }} Uhr
  </div>

  <!-- Action buttons -->
  <div v-if="randomStop" class="button-wrapper">
    <button class="option-btn" @click="reset()">Zurücksetzen</button>
    <button class="option-btn" @click="getRandomStop()">Erneut Würfeln</button>
    <button class="option-btn active" @click="saveJourney()">Bestätigen</button>
  </div>

  <!-- Journeys overview -->
  <div v-if="journeysStorage.length > 0" class="card">
    <h2>Deine Reise</h2>
    <div class="journey-list">
      <div v-for="(journey, i) in journeysStorage" :key="i" class="journey-item">
        <table class="journey">
          <tbody>
            <tr>
              <td>{{ journey.from.departure }}</td>
              <td>{{ journey.from.station_name }}</td>
            </tr>
            <tr>
              <td>{{ journey.to.arrival }}</td>
              <td>{{ journey.to.station_name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <button class="btn danger" @click="journeysStorage = []">Reise Löschen</button>
  </div>

</template>

<style scoped>
.search-wrapper {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  margin-bottom: 1em;
  min-width: 280px;
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box input {
  background: none;
  border: none;
  color: var(--text-primary);
  font-size: 16px;
  width: 100%;
  font-family: inherit;
  outline: none;
}

ul {
  margin: 0;
}

ul li {
  cursor: pointer;
}

.action-btn.primary {
  background: var(--accent-gradient);
  border: none;
  color: var(--bg-primary);
}

.action-btn {
  display: flex;
  align-items: center;
  margin-top: 1em;
  gap: 10px;
  padding: 14px 24px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid var(--border);
  background-color: var(--bg-card);
  color: var(--text-primary);
}

.action-btn.primary:hover {
  background: var(--accent-gradient-hover);
  transform: translateY(-2px);
}

.card {
  background-color: var(--bg-card);
  border-radius: 14px;
  padding: 28px;
  border: 1px solid var(--border);
  transition: all 0.3s ease;
}

.card h2 {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-primary);
  margin-top: 0;
}

.card:not(:first-child) {
  margin-top: 1em;
}

.button-wrapper {
  display: flex;
  gap: 8px;
  margin-top: 1em;
}

.option-btn.active {
  background: var(--accent-gradient);
  color: var(--bg-primary);
  border: none;
  font-weight: bold;
}

.option-btn {
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  background: none;
  border: 1px solid var(--border);
  color: var(--text-secondary);
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
}

.option-btn:hover {
  color: var(--text-primary);
  border-color: var(--accent-copper);
}

.journey-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.journey-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background-color: rgba(255, 255, 255, 0.03);
  border-radius: 10px;
  transition: all 0.2s ease;
}

.journey-item:hover {
  background-color: rgba(255, 255, 255, 0.06);
}

.journey-item:last-child {
  margin-bottom: 1em;
}

table.journey {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

table.journey td:nth-child(1) {
  width: 3.5em;
}

.btn.danger {
  color: var(--loss);
  border-color: var(--loss);
}

.btn.danger:hover {
  background-color: rgba(194, 120, 120, 0.15);
}

.btn {
  padding: 14px 28px;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid var(--border);
  background-color: transparent;
  color: var(--text-primary);
}

.btn:hover {
  background-color: var(--bg-card-hover);
}
</style>
