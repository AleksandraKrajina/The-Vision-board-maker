<template>
  <div class="app">
    <header class="topbar">
      <div class="brand">✨ Vision Board Maker <span class="badge">Vue 3</span></div>
      <div class="actions">
        <select v-model="lang" class="lang-select">
          <option value="sr">Srpski</option>
          <option value="en">English</option>
          <option value="de">Deutsch</option>
        </select>
        <button class="nav-btn" @click="view = 'landing'">{{ t('landing') }}</button>
        <button class="nav-btn" @click="view = 'creator'">{{ t('creator') }}</button>
      </div>
    </header>

    <section v-if="view === 'landing'" class="hero">
      <div class="hero-inner">
        <h1>{{ t('title') }}</h1>
        <p>{{ t('subtitle') }}</p>
        <button class="cta" @click="view = 'creator'">{{ t('cta') }}</button>
      </div>
    </section>

    <main v-else class="creator-page">
      <aside class="panel">
        <h2>{{ t('boardSettings') }}</h2>

        <label>{{ t('boardSize') }}</label>
        <select v-model="size" @change="applySize">
          <option value="1920x1080">Laptop</option>
          <option value="2048x1536">Tablet</option>
          <option value="1170x2532">Phone</option>
          <option value="7016x9933">A1</option>
          <option value="4961x7016">A2</option>
        </select>

        <label>{{ t('category') }}</label>
        <select v-model="category">
          <option value="travel">{{ t('travel') }}</option>
          <option value="work">{{ t('work') }}</option>
          <option value="love">{{ t('love') }}</option>
          <option value="star">{{ t('star') }}</option>
        </select>

        <input v-model="imageUrl" type="url" placeholder="https://..." />
        <button class="cta small" @click="addFromUrl">{{ t('addByUrl') }}</button>

        <input type="file" accept="image/*" @change="addFromFile" />
        <button class="nav-btn" @click="printBoard">{{ t('download') }}</button>

        <button class="nav-btn" @click="connectWallet">{{ t('connectWallet') }}</button>
        <small>{{ walletStatus }}</small>
      </aside>

      <section class="board-wrap">
        <div class="board" ref="board">
          <p v-if="!stickers.length" class="placeholder">{{ t('placeholder') }}</p>
          <img v-for="item in stickers" :key="item.id" :src="item.src" class="sticker"
            :style="{ left: item.x + 'px', top: item.y + 'px' }" @mousedown="startDrag($event, item.id)" />
        </div>
      </section>
    </main>

    <section v-if="view === 'landing'" class="content">
      <article>
        <h3>{{ t('why') }}</h3>
        <p>{{ t('whyText') }}</p>
      </article>
      <article>
        <h3>{{ t('benefits') }}</h3>
        <p>{{ t('benefitsText') }}</p>
      </article>
    </section>
  </div>
</template>
<script>

//import html2canvas from 'html2canvas';

import { translations } from './i18n/translations.js';

export default {
  data() {
    return {
      lang: localStorage.getItem('lang') || 'sr',
      view: 'landing',
      size: '1920x1080',
      category: 'travel',
      imageUrl: '',
      stickers: [],
      walletStatus: translations.sr.walletOff,
      dragId: null,
      dragOffsetX: 0,
      dragOffsetY: 0
    };
  },
  watch: {
    lang(v) {
      localStorage.setItem('lang', v);
      if (!this.walletStatus.includes('0x')) this.walletStatus = this.t('walletOff');
    }
  },
  methods: {
    t(key) {
      return translations[this.lang]?.[key] || key;
    },
    applySize() {
      const [w, h] = this.size.split('x').map(Number);
      const width = 760;
      const height = Math.round((h / w) * width);
      this.$refs.board.style.width = `${width}px`;
      this.$refs.board.style.height = `${height}px`;
    },
    nextPosition() {
      const board = this.$refs.board;
      const maxX = Math.max(10, board.clientWidth - 160);
      const maxY = Math.max(10, board.clientHeight - 160);
      if (this.category === 'star') return { x: maxX / 2, y: maxY / 2 };
      return { x: Math.floor(Math.random() * maxX), y: Math.floor(Math.random() * maxY) };
    },
    addSticker(src) {
      if (!src) return;
      const pos = this.nextPosition();
      this.stickers.push({ id: Date.now() + Math.random(), src, x: pos.x, y: pos.y });
    },
    addFromUrl() {
      this.addSticker(this.imageUrl.trim());
      this.imageUrl = '';
    },
    addFromFile(e) {
      const file = e.target.files?.[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => this.addSticker(reader.result);
      reader.readAsDataURL(file);
      e.target.value = '';
    },
    printBoard() {
      window.print();
    },
    async connectWallet() {
      if (!window.ethereum) {
        this.walletStatus = this.t('walletOff');
        return;
      }
      try {
        const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
        const wallet = accounts?.[0] || '';
        this.walletStatus = wallet ? `${wallet.slice(0, 6)}...${wallet.slice(-4)}` : this.t('walletOff');
      } catch {
        this.walletStatus = this.t('walletOff');
      }
    },
    startDrag(event, id) {
      const item = this.stickers.find((s) => s.id === id);
      if (!item) return;
      this.dragId = id;
      this.dragOffsetX = event.clientX - item.x;
      this.dragOffsetY = event.clientY - item.y;
    },
    onMouseMove(event) {
      if (!this.dragId) return;
      const board = this.$refs.board;
      const maxX = board.clientWidth - 150;
      const maxY = board.clientHeight - 150;
      const item = this.stickers.find((s) => s.id === this.dragId);
      if (!item) return;
      item.x = Math.max(0, Math.min(maxX, event.clientX - this.dragOffsetX));
      item.y = Math.max(0, Math.min(maxY, event.clientY - this.dragOffsetY));
    },
    onMouseUp() {
      this.dragId = null;
    }
  },
  mounted() {
    this.applySize();
    window.addEventListener('mousemove', this.onMouseMove);
    window.addEventListener('mouseup', this.onMouseUp);
  },
  unmounted() {
    window.removeEventListener('mousemove', this.onMouseMove);
    window.removeEventListener('mouseup', this.onMouseUp);
  }
};

</script>

<style>
@import './assets/styles.css';
</style>
