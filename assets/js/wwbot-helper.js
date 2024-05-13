if (!window.bh_getAllParamsFromUrl) {
  window.bh_getAllParamsFromUrl = function () {
    const urlParamsList = ['utm_source','utm_medium','utm_campaign','utm_id','utm_term','utm_content','gclid','ga_client','cid','sid', 'cp1','cp2'];

    var url = new URL(window.location.href);
    var searchParams = new URLSearchParams(url.search);

    var urlParams = urlParamsList
      .map(function (p) { return [p, searchParams.get(p)]; })
      .reduce(function (acc, pair) { acc[pair[0]] = pair[1]; return acc; }, {});

    return urlParams;
  };
}


if (!window.bh_getOrSetUrlParams) {
  window.bh_getOrSetUrlParams = function () {
    var urlParams;

    urlParams = JSON.parse(localStorage.getItem("_upm")) || JSON.parse(localStorage.getItem("bh_upm"));

    if (!urlParams || urlParams === '{}') {
      urlParams = getAllParamsFromUrl();
      localStorage.setItem("bh_upm", JSON.stringify(urlParams));
    }

    return urlParams;
  };
}

if (!window.getUpdatedHealyLink) {
  window.getUpdatedHealyLink = (healyLink) => {
    if (!window?.posthog) return healyLink;

    let posthogId = window?.posthog?.get_distinct_id();
    let utmParams = window.bh_getAllParamsFromUrl();

    const url = new URL(healyLink);
    const searchParams = new URLSearchParams(url.search);

    searchParams.set("cuid", posthogId);

    if (utmParams)
      Object.entries(utmParams).forEach(param => {
        if (!searchParams.has(param[0])) searchParams.set(param[0], param[1]);
      });

    return `${url.origin}${url.pathname}?${searchParams.toString()}`;
  }
}


window.isMobileDevice = (Math.min(window.screen.width, window.screen.height) < 768 || navigator.userAgent.indexOf("Mobi") > -1);

window.wwchatHead = `
<style>
.dr-cta-card {
  width: 100%;
  max-width: 100%;
  border-radius: 16px;
  background: #ecf0f1;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}
.dr-cta-card p {
  font-size: 16px;
  font-weight: 600;
  color: #000000;
  line-height: 20px;
}
.card-body {
  background: #c9d9ca;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 26px 15px;
}
.card-body .ratings p{
  font-size: 14px;
  margin-left: 6px;
}
.card-body .ratings{
  margin-top: 10px;
}
.card-body .ratings img{
  max-height: 20px;
}
.dr-img-wrapper {
  max-width: 100px;
  width: 100%;
}
.dr-img-wrapper img {
  width: 100%;
  border-radius: 9999px;
}
.content h2 {
  color: #315655;
  font-size: 20px;
  font-weight: 700;
  margin: 0px;
  font-family: poppins;
}
.heading-wrapper {
  display: flex;
  align-items: center;
}
.heading-wrapper img {
  max-width: 32px;
}
.card-footer {
  background: #a8cbaa;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 8px;
}
.contact-links-wrapper {
  display: flex;
  align-items: center;
  gap: 5px;
}
.contact-link {
  display: block;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #c3835a;
  display: flex;
  align-items: center;
  justify-content: center;
}
.contact-link img {
  max-width: 18px;
}
.ratings {
  display: flex;
  align-items: center;
}
.ratings img {
  max-height: 10px;
  height: 100%;
}
.ratings p {
  font-size: 10px;
  font-weight: 500;
}
@media screen and (max-width: 1024px){
  .card-body{
    padding: 18px 8px;
  }
  .content h2{
    font-size:14px;
  }
  .heading-wrapper img {
    max-width: 22px;
  }
  .dr-cta-card p{
    font-size: 12px;
    line-height: 15px;
  }
  .card-body .ratings img {
    max-height: 12px;
  }
  .card-body .ratings p {
    font-size: 10px;
    font-weight: 600;
  }
  .card-body .ratings {
    margin-top: 5px;
  }
  .dr-img-wrapper {
    max-width: 74px;
  }
}
</style>

<div class="wwchathead dr-cta-card-wrapper">
      <div class="dr-cta-card">
        <div class="card-body">
          <div class="dr-img-wrapper">
            <img
              src="#THEMEPATH/assets/images/doctor.png"
              alt="Dr. Chance Miller"
            />
          </div>
          <div>
          <div class="content">
            <div class="heading-wrapper">
              <h2>Dr. Chance Miller, MD</h2>
              <img src="#THEMEPATH/assets/images/tick-badge.png" alt="badge" />
            </div>
            <p>
              Dr. Miller is a young and energetic, open-minded physician at the
              forefront of holistic medicine.
            </p>
          </div>
          <div class="ratings">
            <img
              src="#THEMEPATH/assets/images/star.png"
              alt="stars"
            />
            <p>55,255 Satisfied Customers</p>
          </div>
          </div>
        </div>
      </div>
    </div>
`

