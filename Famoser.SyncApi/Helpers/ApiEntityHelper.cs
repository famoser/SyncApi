using System;
using Famoser.SyncApi.Api.Communication.Entities;
using Famoser.SyncApi.Api.Communication.Entities.Base;
using Famoser.SyncApi.Enums;
using Famoser.SyncApi.Models.Information;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Helpers
{
    public class ApiEntityHelper
    {
        #region create sync entity
        private static T CreateApiEntity<T>(CacheInformations info, string identifier, Func<object> getModelFunc)
            where T : BaseEntity, new()
        {
            var modl = new T()
            {
                Id = info.Id,
                VersionId = info.VersionId,
                OnlineAction = Convert(info.PendingAction)
            };
            if (info.PendingAction == PendingAction.Create || info.PendingAction == PendingAction.Update)
            {
                modl.CreateDateTime = info.CreateDateTime;
                modl.Content = JsonConvert.SerializeObject(getModelFunc.Invoke());
            }
            if (info.PendingAction == PendingAction.Create)
                modl.Identifier = identifier;

            return modl;
        }

        public static CollectionEntity CreateCollectionEntity(CacheInformations info, string identifier, Func<object> getModelFunc)
        {
            return CreateApiEntity<CollectionEntity>(info, identifier, getModelFunc);
        }

        public static SyncEntity CreateSyncEntity(CacheInformations info, string identifier, Func<object> getModelFunc)
        {
            var mdl = CreateApiEntity<SyncEntity>(info, identifier, getModelFunc);
            if (mdl != null)
                mdl.CollectionId = info.CollectionId;
            return mdl;
        }

        private static OnlineAction Convert(PendingAction action)
        {
            switch (action)
            {
                case PendingAction.Create: return OnlineAction.Create;
                case PendingAction.Read: return OnlineAction.Read;
                case PendingAction.Update: return OnlineAction.Update;
                case PendingAction.Delete: return OnlineAction.Delete;
                case PendingAction.None: return OnlineAction.ConfirmVersion;
                default:
                    return OnlineAction.None;
            }
        }
        #endregion

        #region create cache info
        private static T CreateCacheInformation<T>(BaseEntity entity, T existing = null)
            where T : CacheInformations, new()
        {
            if (existing == null)
                existing = new T();
            existing.Id = entity.Id;
            existing.VersionId = entity.VersionId;
            existing.PendingAction = PendingAction.None;
            existing.CreateDateTime = entity.CreateDateTime;
            return existing;
        }

        public static T CreateCacheInformation<T>(CollectionEntity entity, T exisiting = null)
            where T : CacheInformations, new()
        {
            var mi = CreateCacheInformation(entity as BaseEntity, exisiting);
            mi.UserId = entity.UserId;
            mi.DeviceId = entity.DeviceId;
            return mi;
        }

        public static HistoryInformations<T> CreateHistoryInformation<T>(CollectionEntity entity)
        {
            return CreateCacheInformation(entity, new HistoryInformations<T>());
        }

        public static T CreateCacheInformation<T>(SyncEntity entity, T existing = null)
            where T : CacheInformations, new()
        {
            var mi = CreateCacheInformation(entity as CollectionEntity, existing);
            mi.CollectionId = entity.CollectionId;
            return mi;
        }
        #endregion
    }
}
