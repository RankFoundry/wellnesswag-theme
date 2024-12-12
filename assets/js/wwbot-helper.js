if (!window.bh_getAllParamsFromUrl) {
  window.bh_getAllParamsFromUrl = function () {
    const urlParamsList = [
      "utm_source",
      "utm_medium",
      "utm_campaign",
      "utm_id",
      "utm_term",
      "utm_content",
      "gclid",
      "ga_client",
      "cid",
      "sid",
      "cp1",
      "cp2",
    ];

    var url = new URL(window.location.href);
    var searchParams = new URLSearchParams(url.search);

    var urlParams = urlParamsList
      .map(function (p) {
        return [p, searchParams.get(p)];
      })
      .reduce(function (acc, pair) {
        acc[pair[0]] = pair[1];
        return acc;
      }, {});

    return urlParams;
  };
}

if (!window.bh_getOrSetUrlParams) {
  window.bh_getOrSetUrlParams = function () {
    var urlParams;

    urlParams =
      JSON.parse(localStorage.getItem("_upm")) ||
      JSON.parse(localStorage.getItem("bh_upm"));

    if (!urlParams || urlParams === "{}") {
      urlParams = getAllParamsFromUrl();
      localStorage.setItem("bh_upm", JSON.stringify(urlParams));
    }

    return urlParams;
  };
}

if (!window.getUpdatedHealyLink) {
  window.getUpdatedHealyLink = (healyLink) => {
    // First check if PostHog exists and has the required method
    if (!window?.posthog?.identify) return healyLink;

    // Get PostHog ID safely using the proper method
    let posthogId;
    try {
      posthogId =
        window.posthog.get_session_id() || window.posthog.get_distinct_id();
    } catch (e) {
      console.warn("Unable to get PostHog ID:", e);
      return healyLink;
    }

    // If no PostHog ID, return original link
    if (!posthogId) return healyLink;

    try {
      // Get UTM parameters if the function exists
      let utmParams =
        typeof window.bh_getAllParamsFromUrl === "function"
          ? window.bh_getAllParamsFromUrl()
          : {};

      const url = new URL(healyLink);
      const searchParams = new URLSearchParams(url.search);

      // Set customer ID
      searchParams.set("cuid", posthogId);

      // Add UTM parameters if they exist
      if (utmParams && typeof utmParams === "object") {
        Object.entries(utmParams).forEach(([key, value]) => {
          if (!searchParams.has(key)) {
            searchParams.set(key, value);
          }
        });
      }

      return `${url.origin}${url.pathname}?${searchParams.toString()}`;
    } catch (e) {
      console.warn("Error updating Healy link:", e);
      return healyLink;
    }
  };
}

window.isMobileDevice =
  Math.min(window.screen.width, window.screen.height) < 768 ||
  navigator.userAgent.indexOf("Mobi") > -1;

window.wwchatHead = `
<style>
#bot-desktop{
  position: relative;
  z-index: 1;
}
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
.card-body-chat {
  background: #083339;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
}
.card-body-chat .ratings p{
  font-size: 14px;
  margin-left: 6px;
  color: #ffffff;
}
.card-body-chat .ratings{
  margin-top: 10px;
}
.card-body-chat .ratings img{
  max-height: 20px;
}
.dr-img-wrapper {
  max-width: 80px;
  width: 100%;
}
.dr-img-wrapper img {
  width: 100%;
}
.content h2 {
  color: #ffffff;
  font-size: 28px;
  line-height: 33px;
  font-weight: 700;
  margin: 0px;
  font-family: Lato;
}
.content p{
  color: #ffffff;
  font-size: 20px;
  font-weight: 600;
  line-height: 28px;
}
.heading-wrapper {
  display: flex;
  align-items: center;
  margin-bottom: 4px;
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
  font-size: 16px;
  font-weight: 500;
  line-height: 22px;
}
@media screen and (max-width: 1024px){
  .card-body-chat{
    padding: 16px;
  }
  .content h2{
    font-size:14px;
  }
  .heading-wrapper{
  margin-bottom: 0px;
   }
  .heading-wrapper img {
    max-width: 22px;
  }
  .dr-cta-card p{
    font-size: 12px;
    line-height: 15px;
  }
  .card-body-chat .ratings img {
    max-height: 12px;
  }
  .card-body-chat .ratings p {
    font-size: 10px;
    font-weight: 600;
  }
  .card-body-chat .ratings {
    margin-top: 5px;
  }
  .dr-img-wrapper {
    max-width: 74px;
  }
}
</style>

<div class="wwchathead dr-cta-card-wrapper">
      <div class="dr-cta-card">
        <div class="card-body-chat d-none">
          <div class="dr-img-wrapper">
            <img
              src="#THEMEPATH/assets/images/d-cb-profile-img.png"
              alt="Dr. Chance Miller"
            />
          </div>
          <div>
          <div class="content">
            <div class="heading-wrapper">
              <h2>Dr. J. Chance Miller</h2>
            </div>
            <p>
              Emotional Support Animal Doctor.
            </p>
          </div>
          <div class="ratings">
            <img
              src="#THEMEPATH/assets/images/star.png"
              alt="stars"
            />
            <p>12,876 Satisfied costumers</p>
          </div>
          </div>
        </div>
      </div>
    </div>
`;
