function generateString(length) {
    const characters ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    const charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

// Get a cookie
const getCookie = name => {
    const encodedName = encodeURIComponent(name);
    const cookies = document.cookie.split(';').map(cookie => cookie.trim());

    for (const cookie of cookies) {
        if (cookie.startsWith(encodedName + '=')) {
            const encodedValue = cookie.substring(encodedName.length + 1);
            return decodeURIComponent(encodedValue);
        }
    }
    return null;
};
// Set a cookie
const setCookie = (name, value, days) => document.cookie = `${name}=${value}; expires=${new Date(Date.now() + days * 86400000).toUTCString()}; path=/`;
// Delete a cookie
const deleteCookie = name => setCookie(name, '', -1);

const getSpecifiedUrlParams = () => {
    function getParameterByName(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        const regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    const parameters = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid','ga_client','cuid','creferrer','cid','sid'];
    let trackingData = {};

    parameters.forEach(param => {
        const value = getParameterByName(param);
        if (value) trackingData[param] = value;
    });

    return trackingData;
}

const getGivenUrlParam = (paramKey) => {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    let uParam = searchParams.get(paramKey);

    return uParam;
}

const getOrSetCuid = () => {
    let cuid;

    cuid = getCookie('cuid');

    if(!cuid) cuid = localStorage.getItem("cuid")

    if(!cuid) {
        cuid = getGivenUrlParam("cuid") || generateString(10);
        localStorage.setItem("cuid", cuid);
        setCookie('cuid', cuid);
    }

    return cuid;
}

const getOrSetCReferrer = () => {
    let creferrer;

    creferrer = getCookie('creferrer');

    if(!creferrer) creferrer = localStorage.getItem("creferrer")
    
    if(!creferrer) {
        creferrer = getGivenUrlParam("creferrer") || document.referrer || null;
        localStorage.setItem("creferrer", creferrer);
        setCookie('creferrer', creferrer);
    }

    return creferrer;
}

const getOrSetUtmParams = () => {
    let utmParams;

    utmParams = JSON.parse(getCookie('_utd'));

    if(!utmParams || utmParams === '{}') {
        utmParams = getSpecifiedUrlParams();
        setCookie('_utd', JSON.stringify(utmParams), 30);
        localStorage.setItem("_utd", JSON.stringify(utmParams));
    }

    return utmParams;
}

const getUpdatedHealyLink = (healyLink) => {
    let customUserId = getOrSetCuid();
    let creferrer = getOrSetCReferrer();
    let utmParams = getOrSetUtmParams();

    const url = new URL(healyLink);
    const searchParams = new URLSearchParams(url.search);

    searchParams.set("cuid", customUserId);
    searchParams.set("creferrer", creferrer);

    if(utmParams)
        Object.entries(utmParams).forEach(param => {
            if(!searchParams.has(param[0])) searchParams.set(param[0], param[1]);
        });
    
    return `${url.origin}${url.pathname}?${searchParams.toString()}`;
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
              src="${window.themePath}/assets/images/doctor.png"
              alt="Dr. Chance Miller"
            />
          </div>
          <div>
          <div class="content">
            <div class="heading-wrapper">
              <h2>Dr. Chance Miller, MD</h2>
              <img src="${window.themePath}/assets/images/tick-badge.png" alt="badge" />
            </div>
            <p>
              Dr. Miller is a young and energetic, open-minded physician at the
              forefront of holistic medicine.
            </p>
          </div>
          <div class="ratings">
            <img
              src="${window.themePath}/assets/images/star.png"
              alt="stars"
            />
            <p>55,255 Satisfied Customers</p>
          </div>
          </div>
        </div>
      </div>
    </div>
`

